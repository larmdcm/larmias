<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue\Driver;

use Larmias\AsyncQueue\Contracts\MessageInterface;
use Larmias\AsyncQueue\Contracts\QueueStatusInterface;
use Larmias\AsyncQueue\Exceptions\QueueException;
use Larmias\AsyncQueue\Message\QueueStatus;
use Larmias\Contracts\Redis\ConnectionInterface;
use Larmias\Contracts\Redis\RedisFactoryInterface;
use Larmias\Stringable\Str;
use Throwable;
use function time;
use function in_array;
use function Larmias\Support\throw_unless;

class Redis extends QueueDriver
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
        // 任务处理超时时间
        'handle_timeout' => 0,
        // 等待时间（秒）
        'wait_time' => 1,
        // 消费间隔（秒）
        'timespan' => 1,
    ];

    /**
     * @var ConnectionInterface
     */
    protected ConnectionInterface $redis;

    /**
     * @param RedisFactoryInterface $factory
     * @return void
     */
    public function initialize(RedisFactoryInterface $factory): void
    {
        $this->redis = $factory->get($this->config['redis_name']);
    }

    /**
     * @param MessageInterface $message
     * @param int $delay
     * @return MessageInterface
     * @throws Throwable
     */
    public function push(MessageInterface $message, int $delay = 0): MessageInterface
    {
        $message->setMessageId(Str::random(32));
        $message->setAttempts($message->getAttempts() + 1);
        $data = $this->packer->pack($message);
        $queue = $message->getQueue();
        $result = $delay > 0 ? $this->redis->zAdd($this->getDelayed($queue), time() + $delay, $data) > 0 : $this->redis->lPush($this->getWaiting($queue), $data);
        throw_unless($result, QueueException::class, $this->redis->getLastError() ?: 'Queue push failed.');
        return $message;
    }

    /**
     * @param float $timeout
     * @param string|null $queue
     * @return MessageInterface|null
     */
    public function pop(float $timeout = 0, ?string $queue = null): ?MessageInterface
    {
        $this->move($this->getDelayed($queue), $this->getWaiting($queue));
        $this->move($this->getReserved($queue), $this->getTimeout($queue));
        $timeout = (int)$timeout;
        $waitQueue = $this->getWaiting($queue);
        $res = $timeout > 0 ? $this->redis->brPop($waitQueue, $timeout) : $this->redis->lPop($waitQueue);
        if (!isset($res[1])) {
            return null;
        }

        $data = $res[1];
        $message = $this->packer->unpack($data);
        if (!$message) {
            return null;
        }

        if ($this->config['handle_timeout'] > 0) {
            $this->redis->zadd($this->getReserved($queue), time() + $this->config['handle_timeout'], $data);
        }

        return $message;
    }

    /**
     * @param MessageInterface $message
     * @return bool
     */
    public function ack(MessageInterface $message): bool
    {
        return $this->delete($message);
    }

    /**
     * @param MessageInterface $message
     * @return bool
     */
    public function fail(MessageInterface $message): bool
    {
        if ($this->delete($message)) {
            return (bool)$this->redis->lPush($this->getFailed($message->getQueue()), $this->packer->pack($message));
        }
        return false;
    }

    /**
     * @param MessageInterface $message
     * @return bool
     */
    public function delete(MessageInterface $message): bool
    {
        return $this->redis->zRem($this->getReserved($message->getQueue()), $this->packer->pack($message)) > 0;
    }

    /**
     * @param string|null $queue
     * @param string|null $type
     * @return bool
     */
    public function flush(?string $queue = null, ?string $type = null): bool
    {
        if ($type) {
            if (!in_array($type, ['waiting', 'reserved', 'delayed', 'failed', 'timeout'])) {
                throw new QueueException(sprintf('Queue %s is not supported.', $type));
            }
            $channel = $this->getQueueKey($queue) . ':' . $type;
        } else {
            $channel = $this->getFailed($queue);
        }

        return (bool)$this->redis->del($channel);
    }

    /**
     * @param string|null $queue
     * @return QueueStatusInterface
     */
    public function status(?string $queue = null): QueueStatusInterface
    {
        return QueueStatus::formArray([
            'waiting' => $this->redis->lLen($this->getWaiting($queue)),
            'delayed' => $this->redis->zCard($this->getDelayed($queue)),
            'failed' => $this->redis->lLen($this->getFailed($queue)),
            'timeout' => $this->redis->lLen($this->getTimeout($queue)),
        ]);
    }

    /**
     * @param string|null $queue
     * @return int
     */
    public function reloadFailMessage(?string $queue = null): int
    {
        return $this->reloadMessage($queue, 'failed');
    }

    /**
     * @param string|null $queue
     * @return int
     */
    public function reloadTimeoutMessage(?string $queue = null): int
    {
        return $this->reloadMessage($queue, 'timeout');
    }

    /**
     * @param string|null $queue
     * @param string|null $type
     * @return int
     */
    protected function reloadMessage(?string $queue = null, ?string $type = null): int
    {
        if (!in_array($type, ['failed', 'timeout'])) {
            throw new QueueException(sprintf('Queue %s is not supported.', $type));
        }
        $channel = $this->getQueueKey($queue) . ':' . $type;
        $num = 0;
        while ($this->redis->rpoplpush($channel, $this->getWaiting($queue))) {
            ++$num;
        }
        return $num;
    }

    /**
     * @param string $from
     * @param string $to
     * @return void
     */
    protected function move(string $from, string $to): void
    {
        $now = time();
        $options = ['LIMIT' => [0, 100]];
        if ($expired = $this->redis->zRevRangeByScore($from, (string)$now, '-inf', $options)) {
            foreach ($expired as $job) {
                if ($this->redis->zRem($from, $job) > 0) {
                    $this->redis->lPush($to, $job);
                }
            }
        }
    }

    protected function getWaiting(?string $name): string
    {
        return $this->getQueueKey($name) . ':waiting';
    }

    protected function getReserved(?string $name): string
    {
        return $this->getQueueKey($name) . ':reserved';
    }

    protected function getDelayed(?string $name): string
    {
        return $this->getQueueKey($name) . ':delayed';
    }

    protected function getTimeout(?string $name): string
    {
        return $this->getQueueKey($name) . ':timeout';
    }

    public function getFailed(?string $name): string
    {
        return $this->getQueueKey($name) . ':failed';
    }
}