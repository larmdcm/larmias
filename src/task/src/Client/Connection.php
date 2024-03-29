<?php

declare(strict_types=1);

namespace Larmias\Task\Client;

use Larmias\Client\AsyncSocket;
use Larmias\Codec\Protocol\FrameProtocol;
use Larmias\Contracts\Client\AsyncSocketInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\SharedMemory\Client\Connection as BaseClient;
use Larmias\SharedMemory\Message\Result;
use Larmias\Task\Task;
use function is_array;
use function implode;
use function call_user_func_array;

class Connection extends BaseClient
{
    /**
     * @var SyncWait
     */
    public SyncWait $syncWait;

    /**
     * @var AsyncSocketInterface
     */
    protected AsyncSocketInterface $asyncSocket;

    /**
     * @param ContainerInterface $container
     * @param array $options
     * @throws \Throwable
     */
    public function __construct(ContainerInterface $container, array $options = [])
    {
        $options['async'] = true;
        $options['event'][self::EVENT_CONNECT] = fn(Connection $client) => $this->onConnect($client);
        $options['timeout'] = $options['timeout'] ?? 3;
        $this->syncWait = $container->get(SyncWait::class);
        parent::__construct($options);
    }

    /**
     * @var callable[]
     */
    protected array $callbacks = [];

    /**
     * @param Connection $client
     * @return void
     */
    protected function onConnect(Connection $client): void
    {
        $this->asyncSocket = new AsyncSocket(Connection::getEventLoop(), $client->getSocket());
        $this->asyncSocket->set([
            'protocol' => FrameProtocol::class,
        ]);
        $this->asyncSocket->on(AsyncSocketInterface::ON_MESSAGE, function (mixed $data) {
            $result = Result::parse($data);
            if (!$result->success || !is_array($result->data) || !isset($result->data['type'])) {
                return;
            }
            switch ($result->data['type']) {
                case 'publish':
                    $this->triggerEvent(['publish', $result->data['task_id']], $result->data);
                    break;
                case 'subscribe':
                    $this->triggerEvent(['subscribe', $result->data['name']], $result->data);
                    break;
                case 'getInfo':
                    $this->triggerEvent(['getInfo', $result->data['key']], $result->data['value']);
                    break;
                case 'setInfo':
                    $this->triggerEvent(['setInfo', $result->data['key']], $result->data);
                    break;
                case 'message':
                    $this->triggerEvent(['message', $result->data['name']], $result->data);
                    break;
                case 'finish':
                    if (isset($result->data['task_id'])) {
                        $this->taskFinish($result->data['task_id'], $result->data['result'] ?? null);
                    }
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
        $taskId = $task->getId();
        $result = $this->sendCommand('task:publish', [$task]);
        if ($result) {
            $this->syncWait->add($taskId);
            $this->listen([__FUNCTION__, $taskId], $callback, true);
        }
        return $result;
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
     * @param string $taskId
     * @param mixed $result
     * @return bool
     */
    public function finish(string $taskId, mixed $result): bool
    {
        return $this->sendCommand('task:finish', [$taskId, $result]);
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
     * @param string $taskId
     * @param mixed $result
     * @return void
     */
    protected function taskFinish(string $taskId, mixed $result): void
    {
        $this->syncWait->done($taskId, $result);
    }

    /**
     * @param string|array $name
     * @param callable|null $callback
     * @param bool $once
     * @return void
     */
    protected function listen(string|array $name, ?callable $callback = null, bool $once = false): void
    {
        if (is_array($name)) {
            $name = implode('.', $name);
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
    protected function triggerEvent(string|array $name, ...$args): mixed
    {
        if (is_array($name)) {
            $name = implode('.', $name);
        }
        if (isset($this->callbacks[$name])) {
            $result = call_user_func_array($this->callbacks[$name]['callback'], $args);
            if ($this->callbacks[$name]['once']) {
                unset($this->callbacks[$name]);
            }
            return $result;
        }
        return false;
    }
}