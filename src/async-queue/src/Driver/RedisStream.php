<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue\Driver;

use Larmias\AsyncQueue\Contracts\QueueStatusInterface;
use Larmias\AsyncQueue\Message\QueueStatus;
use Larmias\Contracts\Redis\ConnectionInterface;
use Larmias\Contracts\Redis\RedisFactoryInterface;
use Larmias\AsyncQueue\Contracts\MessageInterface;
use Larmias\AsyncQueue\Exceptions\QueueException;
use Throwable;
use function array_key_first;
use function Larmias\Support\is_empty;
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
        // 等待时间（秒）
        'wait_time' => 1,
        // 消费间隔（秒）
        'timespan' => 1,
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
    protected ConnectionInterface $redis;

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
        $this->redis = $factory->get($this->config['redis_name']);
        $this->group = $this->config['group'];
        $this->consumer = $this->config['consumer'];
        $this->failConsumer = $this->config['fal_consumer'];
    }

    /**
     * @param MessageInterface $message
     * @param int $delay
     * @return MessageInterface
     * @throws Throwable
     */
    public function push(MessageInterface $message, int $delay = 0): MessageInterface
    {
        $message->setAttempts($message->getAttempts() + 1);
        $data = [
            'message' => $this->packer->pack($message),
            'delay' => $delay,
            'timestamp' => microtime(true),
        ];
        $id = is_empty($message->getMessageId()) ? '*' : $message->getMessageId();
        $messageId = $this->redis->xAdd($this->getQueueKey($message->getQueue()), $id, $data, $this->config['maxlength'], $this->config['approximate']);
        throw_unless($messageId, QueueException::class, $this->redis->getLastError() ?: 'Queue push failed.');
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
        $this->prepareGroup($queue);
        $queueKey = $this->getQueueKey($queue);
        $block = $timeout > 0 ? (int)($timeout * 1000) : null;
        $result = $this->redis->xReadGroup($this->group, $this->consumer, [$queueKey => '>'], 1, $block);
        if (!$result) {
            return null;
        }
        $messageId = array_key_first($result[$queueKey]);
        $data = $result[$queueKey][$messageId];
        if ($data['delay'] > 0 && (($data['delay'] / 1000 + $data['timestamp']) - microtime(true)) > 0) {
            // 延时队列重入
            $this->redis->xAdd($queueKey, '*', $data);
            $this->redis->xAck($queueKey, $this->group, [$messageId]);
            return null;
        }
        /** @var MessageInterface $message */
        $message = $this->packer->unpack($data['message']);
        $message->setMessageId($messageId);
        return $message;
    }

    /**
     * @param MessageInterface $message
     * @return bool
     */
    public function ack(MessageInterface $message): bool
    {
        $result = $this->redis->xAck($this->getQueueKey($message->getQueue()), $this->group, [$message->getMessageId()]);
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
        $result = $this->redis->xClaim($queueKey, $this->group, $this->failConsumer, 0, [$message->getMessageId()], [
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
        $result = $this->redis->xDel($this->getQueueKey($message->getQueue()), [$message->getMessageId()]);
        return $result !== 0;
    }

    /**
     * @param string|null $queue
     * @param string|null $type
     * @return bool
     */
    public function flush(?string $queue = null, ?string $type = null): bool
    {
        return $this->redis->del($this->getQueueKey($queue)) !== 0;
    }

    /**
     * @param string|null $queue
     * @return QueueStatusInterface
     */
    public function status(?string $queue = null): QueueStatusInterface
    {
        $info = $this->redis->xInfo('STREAM', $this->getQueueKey($queue), 'FULL') ?: [];
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
        $result['failed'] = $failConsumerInfo['pel-count'] ?? 0;
        $result['waiting'] = ($groupInfo['pel-count'] ?? 0) - $result['failed'];
        $result['timeout'] = 0;
        $result['delayed'] = 0;

        return QueueStatus::formArray($result);
    }

    /**
     * @param string|null $queue
     * @return int
     */
    public function reloadTimeoutMessage(?string $queue = null): int
    {
        return 0;
    }

    /**
     * @param string|null $queue
     * @return int
     */
    public function reloadFailMessage(?string $queue = null): int
    {
        $queueKey = $this->getQueueKey($queue);
        $start = '0';
        $total = 0;
        while (true) {
            $result = $this->redis->xReadGroup($this->group, $this->failConsumer, [$queueKey => $start], 100);
            if (!$result) {
                break;
            }
            $ids = [];
            foreach ($result[$queueKey] as $messageId => $data) {
                if ($start === $messageId) {
                    continue;
                }
                $ids[] = $start = $messageId;
                $this->redis->xAdd($queueKey, '*', $data, $this->config['maxlength'], $this->config['approximate']);
                $total++;
            }
            if ($ids) {
                $this->redis->xAdd($queueKey, $this->group, $ids);
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
        $this->redis->xGroup('CREATE', $queue, $this->group, '0', true);
    }
}