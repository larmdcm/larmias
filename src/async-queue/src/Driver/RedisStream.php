<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue\Driver;

use Larmias\Contracts\Redis\ConnectionInterface;
use Larmias\Contracts\Redis\RedisFactoryInterface;
use Larmias\AsyncQueue\Contracts\MessageInterface;
use Larmias\AsyncQueue\Exceptions\QueueException;
use Throwable;
use function array_key_first;
use function unserialize;
use function serialize;
use function microtime;
use function Larmias\Support\throw_unless;

class RedisStream extends QueueDriver
{
    /**
     * @var array
     */
    protected array $config = [
        // redis name
        'redis_name' => 'default',
        // 键前缀
        'prefix' => 'queues:',
        // 队列名称
        'name' => 'default',
        // 超时时间
        'timeout' => 0,
        // 队列最大长度
        'maxlength' => 0,
        // 队列最大长度近似模式
        'approximate' => false,
        // 分组
        'group' => 'streamGroup',
        // 消费者
        'consumer' => 'streamConsumer',
        // 失败队列消费者
        'fal_consumer' => 'streamFailConsumer',
    ];

    /**
     * @var ConnectionInterface
     */
    protected ConnectionInterface $connection;

    /**
     * @var array
     */
    protected array $initGroup = [];

    /**
     * @var string
     */
    protected string $group;

    /**
     * @var string
     */
    protected string $consumer;

    /**
     * @var string
     */
    protected string $failConsumer;

    /**
     * @param RedisFactoryInterface $factory
     * @return void
     */
    public function initialize(RedisFactoryInterface $factory): void
    {
        $this->connection = $factory->get($this->config['redis_name']);
        $this->group = $this->config['group'];
        $this->consumer = $this->config['consumer'];
        $this->failConsumer = $this->config['fal_consumer'];
    }

    /**
     * @param MessageInterface $message
     * @param float $delay
     * @return MessageInterface
     * @throws Throwable
     */
    public function push(MessageInterface $message, float $delay = 0): MessageInterface
    {
        $this->prepareGroup($message->getQueue());
        $message->setAttempts($message->getAttempts() + 1);
        $data = [
            'message' => serialize($message),
            'delay' => $delay,
            'timestamp' => microtime(true),
        ];
        $messageId = $this->connection->xAdd($this->getQueueKey($message->getQueue()), '*', $data, $this->config['maxlength'], $this->config['approximate']);
        throw_unless($messageId, QueueException::class, $this->connection->getLastError() ?: 'Queue push failed.');
        $message->setMessageId($messageId);
        return $message;
    }

    /**
     * @param float $timeout
     * @param string|null $queue
     * @return MessageInterface|null
     */
    public function pop(float $timeout = 0, ?string $queue = null): ?MessageInterface
    {
        $queueKey = $this->getQueueKey($queue);
        $block = $timeout > 0 ? (int)($timeout * 1000) : null;
        $result = $this->connection->xReadGroup($this->group, $this->consumer, [$queueKey => '>'], 1, $block);
        if (!$result) {
            return null;
        }
        $messageId = array_key_first($result[$queueKey]);
        $data = $result[$queueKey][$messageId];
        if ($data['delay'] > 0 && (($data['delay'] / 1000 + $data['timestamp']) - microtime(true)) > 0) {
            // 延时队列重入
            $this->connection->xAdd($queueKey, '*', $data);
            $this->connection->xAck($queueKey, $this->group, [$messageId]);
            return null;
        }
        /** @var MessageInterface $message */
        $message = unserialize($data['message']);
        $message->setMessageId($messageId);
        return $message;
    }

    /**
     * @param MessageInterface $message
     * @return bool
     */
    public function ack(MessageInterface $message): bool
    {
        $result = $this->connection->xAck($this->getQueueKey($message->getQueue()), $this->group, [$message->getMessageId()]);
        return $result !== 0;
    }

    /**
     * @param MessageInterface $message
     * @return bool
     * @throws Throwable
     */
    public function fail(MessageInterface $message): bool
    {
        $queueKey = $this->getQueueKey($message->getQueue());
        $result = $this->connection->xClaim($queueKey, $this->group, $this->failConsumer, 0, [$message->getMessageId()], [
            'IDLE' => 0,
            'FORCE',
            'JUSTID',
        ]);
        return $result !== 0;
    }

    /**
     * @param MessageInterface $message
     * @return bool
     */
    public function delete(MessageInterface $message): bool
    {
        $result = $this->connection->xDel($this->getQueueKey($message->getQueue()), [$message->getMessageId()]);
        return $result !== 0;
    }

    /**
     * @param string|null $queue
     * @return bool
     */
    public function flush(?string $queue = null): bool
    {
        return $this->connection->del($this->getQueueKey($queue)) !== 0;
    }

    /**
     * @param string|null $queue
     * @return array
     */
    public function info(?string $queue = null): array
    {
        $info = $this->connection->xInfo('STREAM', $this->getQueueKey($queue), 'FULL') ?: [];
        $groupInfo = null;
        $failConsumerInfo = null;
        foreach ($info['groups'] ?? [] as $group) {
            if ($group['name'] === $this->group) {
                $groupInfo = $group;
                break;
            }
        }
        if ($groupInfo) {
            foreach ($groupInfo['consumers'] ?? [] as $consumer) {
                if ($consumer['name'] === $this->failConsumer) {
                    $failConsumerInfo = $consumer;
                    break;
                }
            }
        }
        $result['fail'] = $failConsumerInfo['pel-count'] ?? 0;
        $result['working'] = ($groupInfo['pel-count'] ?? 0) - $result['fail'];
        $result['ready'] = $result['timeout'] = $result['delay'] = 0;
        return $result;
    }

    /**
     * @return int
     */
    public function restoreFailMessage(): int
    {
        $queueKey = $this->getQueueKey();
        $start = '0';
        $total = 0;
        while (true) {
            $result = $this->connection->xReadGroup($this->group, $this->failConsumer, [$queueKey => $start], 100);
            if (!$result) {
                break;
            }
            $ids = [];
            foreach ($result[$queueKey] as $messageId => $data) {
                if ($start === $messageId) {
                    continue;
                }
                $ids[] = $start = $messageId;
                $this->connection->xAdd($queueKey, '*', $data, $this->config['maxlength'], $this->config['approximate']);
                $total++;
            }
            if ($ids) {
                $this->connection->xAdd($queueKey, $this->group, $ids);
            } else {
                break;
            }
        }
        return $total;
    }

    /**
     * @param string|null $name
     * @return void
     */
    protected function prepareGroup(?string $name = null): void
    {
        $queue = $this->getQueueKey($name);
        $name = $name ?: $this->config['name'];
        if (isset($this->initGroup[$name])) {
            return;
        }
        $this->initGroup[$name] = true;
        $this->connection->xGroup('CREATE', $queue, $this->group, '0', true);
    }
}