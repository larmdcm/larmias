<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue\Driver;

use Larmias\Contracts\Redis\ConnectionInterface;
use Larmias\Contracts\Redis\RedisFactoryInterface;
use Larmias\AsyncQueue\Contracts\MessageInterface;
use Larmias\AsyncQueue\Exceptions\QueueException;
use function array_key_first;
use function unserialize;
use function serialize;
use function microtime;

class RedisStream extends QueueDriver
{
    /**
     * @var array
     */
    protected array $config = [
        // redis name
        'redis_name' => 'default',
        // 键前缀
        'prefix' => 'queue:',
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
     * @var bool
     */
    protected bool $isInitGroup = false;

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
        $this->initGroup();
    }

    /**
     * @param MessageInterface $message
     * @param float $delay
     * @return string
     */
    public function push(MessageInterface $message, float $delay = 0): string
    {
        $message->setAttempts($message->getAttempts() + 1);
        $result = $this->connection->xAdd($this->getQueueKey(), '*', [
            'message' => serialize($message),
            'delay' => $delay,
            'timestamp' => microtime(true),
        ], $this->config['maxlength'], $this->config['approximate']);
        if (!$result) {
            throw new QueueException($this->connection->getLastError() ?: 'Queue push failed.');
        }
        return $result;
    }

    /**
     * @param float $timeout
     * @return MessageInterface|null
     */
    public function pop(float $timeout = 0): ?MessageInterface
    {
        $queueKey = $this->getQueueKey();
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
        $result = $this->connection->xAck($this->getQueueKey(), $this->group, [$message->getMessageId()]);
        return $result !== 0;
    }

    /**
     * @param MessageInterface $message
     * @param bool $reload
     * @return bool
     */
    public function fail(MessageInterface $message, bool $reload = false): bool
    {
        $queueKey = $this->getQueueKey();
        if ($reload) {
            $this->push($message);
            return $this->delete($message);
        }
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
        $result = $this->connection->xDel($this->getQueueKey(), [$message->getMessageId()]);
        if ($result === false) {
            throw new QueueException($this->connection->getLastError() ?: 'Queue delete failed.');
        }
        return $result !== 0;
    }

    /**
     * @return bool
     */
    public function clear(): bool
    {
        return $this->connection->del($this->getQueueKey()) !== 0;
    }

    /**
     * @return array
     */
    public function status(): array
    {
        $info = $this->connection->xInfo('STREAM', $this->getQueueKey(), 'FULL') ?: [];
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
        $status['fail'] = $failConsumerInfo['pel-count'] ?? 0;
        $status['working'] = ($groupInfo['pel-count'] ?? 0) - $status['fail'];
        $status['ready'] = $status['timeout'] = $status['delay'] = 0;
        return $status;
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
     * @return void
     */
    protected function initGroup(): void
    {
        if (!$this->isInitGroup) {
            $this->connection->xGroup('CREATE', $this->getQueueKey(), $this->group, '0', true);
            $this->isInitGroup = true;
        }
    }

    /**
     * @return string
     */
    protected function getQueueKey(): string
    {
        return $this->config['prefix'] . $this->config['name'];
    }
}