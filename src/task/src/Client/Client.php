<?php

declare(strict_types=1);

namespace Larmias\Task\Client;

use Larmias\SharedMemory\Client\Client as BaseClient;
use Larmias\Engine\EventLoop;
use Larmias\Task\Task;

class Client extends BaseClient
{
    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $options['async'] = true;
        $options['event'][self::EVENT_CONNECT] = [$this, 'onConnect'];
        parent::__construct($options);
    }

    /**
     * @var callable[]
     */
    protected array $callbacks = [];

    /**
     * @param Client $client
     * @return void
     */
    public function onConnect(Client $client): void
    {
        EventLoop::onReadable($client->getSocket(), function () use ($client) {
            $result = $client->read();
            if (!$result || !$result->success || !\is_array($result->data) || !isset($result->data['type'])) {
                return;
            }
            switch ($result->data['type']) {
                case 'publish':
                    $this->fireEvent(['publish', $result->data['task_id']], $result->data);
                    break;
                case 'subscribe':
                    $this->fireEvent(['subscribe', $result->data['name']], $result->data);
                    break;
                case 'getInfo':
                    $this->fireEvent(['getInfo', $result->data['key']], $result->data['value']);
                    break;
                case 'setInfo':
                    $this->fireEvent(['setInfo', $result->data['key']], $result->data);
                    break;
                case 'message':
                    $this->fireEvent(['message', $result->data['name']], $result->data);
                    break;
            }
        });
    }

    /**
     * @param Task $task
     * @param callable|null $callback
     * @return bool
     */
    public function publish(Task $task, ?callable $callback = null): bool
    {
        $this->listen([__FUNCTION__, $task->getId()], $callback, true);
        return $this->sendCommand('task:publish', [$task]);
    }

    /**
     * @param string $name
     * @param array $callbacks
     * @return bool
     */
    public function subscribe(string $name, array $callbacks): bool
    {
        $this->listen([__FUNCTION__, $name], $callbacks[0], true);
        $this->listen(['message', $name], $callbacks[1]);
        return $this->sendCommand('task:subscribe', [$name]);
    }

    /**
     * @param string|null $key
     * @param callable $callback
     * @return bool
     */
    public function getInfo(?string $key, callable $callback): bool
    {
        $this->listen([__FUNCTION__, $key], $callback, true);
        return $this->sendCommand('task:getInfo', [$key]);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param callable|null $callback
     * @return bool
     */
    public function setInfo(string $key, mixed $value, ?callable $callback = null): bool
    {
        $this->listen([__FUNCTION__, $key], $callback, true);
        return $this->sendCommand('task:setInfo', [$key, $value]);
    }

    /**
     * @param string|array $name
     * @param callable|null $callback
     * @param bool $once
     * @return void
     */
    protected function listen(string|array $name, ?callable $callback = null, bool $once = false): void
    {
        if (\is_array($name)) {
            $name = \implode('.', $name);
        }
        if ($callback) {
            $this->callbacks[$name] = ['callback' => $callback, 'once' => $once];
        }
    }

    /**
     * @param string|array $name
     * @param ...$args
     * @return mixed
     */
    protected function fireEvent(string|array $name, ...$args): mixed
    {
        if (\is_array($name)) {
            $name = \implode('.', $name);
        }
        if (isset($this->callbacks[$name])) {
            $result = \call_user_func_array($this->callbacks[$name]['callback'], $args);
            if ($this->callbacks[$name]['once']) {
                unset($this->callbacks[$name]);
            }
            return $result;
        }
        return false;
    }
}