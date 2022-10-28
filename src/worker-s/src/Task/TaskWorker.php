<?php

declare(strict_types=1);

namespace Larmias\WorkerS\Task;

use Closure;
use Larmias\WorkerS\Events\EventInterface;
use Larmias\WorkerS\Support\Helper;
use Larmias\WorkerS\Task\Contracts\RWEventInterface;
use Opis\Closure\SerializableClosure;

class TaskWorker
{   
    /**
     * @var int
     */
    const DISPATCH_FIXED = 1;

    /**
     * @var int
     */
    const DISPATCH_BALANCE = 2;

    /**
     * @var Channel
     */
    protected Channel $channel;

    /**
     * @var EventInterface
     */
    protected EventInterface $event;

    /**
     * @var integer
     */
    protected int $intervalTime;

    /**
     * @var array
     */
    protected array $config = [];

    /**
     * @var integer
     */
    protected int $taskWorkerId;

    /**
     * @var string|null
     */
    protected ?string $key = null;

    /**
     * @param int   $taskWorkerId
     * @param array $config
     */
    public function __construct(int $taskWorkerId,array $config = [])
    {
        $this->taskWorkerId = $taskWorkerId;
        $this->config       = array_merge(static::getDefaultConfig(),$config);
        $this->intervalTime = max(1,$config['interval_time'] ?? 1);
        $this->channel      = Channel::create($this,$this->config);
    }

    /**
     * @param  EventInterface $event
     * @return self
     */
    public function setEvent(EventInterface $event): self
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return EventInterface
     */
    public function getEvent(): EventInterface
    {
        return $this->event;
    }

    /**
     * @param callable $callback
     * @param array    $args
     * @return boolean
     */
    public function task(callable $callback,array $args = []): bool
    {
        $data = ['type' => 'callable','callback' => null,'args' => $args];
        if ($callback instanceof Closure) {
            $data['type']     = 'closure';
            $data['callback'] = \serialize(new SerializableClosure($callback));
        } else {
            $data['callback'] = \serialize($callback);
        }
        return $this->channel->push(\serialize($data)) !== null;
    }

    /**
     * @return bool
     */
    public function clear(): bool
    {
        return $this->channel->clear();
    }

    /**
     * @param string|null $item
     * @return bool
     */
    public function runTask(?string $item = null): bool
    {
        if (is_null($item)) {
            $item = $this->channel->shift();
        }
        if (!$item) {
            return false;
        }
        $data = \unserialize($item);
        $task = \unserialize($data['callback']);
        if ($data['type'] === 'closure') {
            \call_user_func($task->getClosure(),$this,$data['args']);
        } else {
            \call_user_func($task,$this,$data['args']);
        }
        return true;
    }

    /**
     * @return void
     */
    public function run(): void
    {
        if ($this->channel instanceof RWEventInterface) {
            $this->event->onReadable($this->channel->getStream(),[$this->channel,'onReadable']);
        } else {
            $this->event->tick($this->intervalTime,function () {
                while ($this->runTask()) {}
            });
        }

        $this->event->run();
    }

    /**
     * @return void
     */
    public function stop(): void
    {
        if (!$this->config['persistent']) {
            $this->clear();
        }
        $this->channel->close();
        $this->event->stop();
    }

    /**
     * @return Channel
     */
    public function getChannel(): Channel
    {
        return $this->channel;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        if (!$this->key) {
            $key = $this->config['key'];
            switch ($this->config['dispatch_mode']) {
                case self::DISPATCH_BALANCE:
                    $key .= '.' . $this->taskWorkerId;
                    break;
            }
            $this->key = $key;
        }
        return $this->key;
    }

    /**
     * @return integer
     */
    public function getTaskWorkerId(): int
    {
        return $this->taskWorkerId;
    }

    /**
     * @return array
     */
    public static function getDefaultConfig(): array
    {
        return [
            'default'       => Helper::isUnix() ? 'unixSocket' : 'file',
            'key'           => 'worker-s.task',
            'dispatch_mode' => self::DISPATCH_BALANCE,
            'persistent'    => false,
            'channels'      => [
                'file'       => [
                    'path' => null,
                ],
                'unixSocket' => [
                    'path' => null,
                ]
            ]
        ];
    }
}