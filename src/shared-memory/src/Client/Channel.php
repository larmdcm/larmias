<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Client;

use Larmias\Client\AsyncSocket;
use Larmias\Codec\Packer\FramePacker;
use Larmias\Contracts\Client\AsyncSocketInterface;
use Larmias\SharedMemory\Message\Result;

class Channel
{
    /**
     * @var array
     */
    protected array $callbacks = [];

    /**
     * @var AsyncSocketInterface
     */
    protected AsyncSocketInterface $asyncSocket;

    /**
     * @var Connection
     */
    protected Connection $conn;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $options['async'] = true;
        $options['event'] = array_merge($options['event'] ?? [], [
            Connection::EVENT_CONNECT => fn(Connection $conn) => $this->onConnect($conn)
        ]);
        $this->conn = new Connection($options);
    }

    /**
     * @param Connection $conn
     * @return void
     */
    protected function onConnect(Connection $conn): void
    {
        $this->asyncSocket = new AsyncSocket(Connection::getEventLoop(), $conn->getSocket());
        $this->asyncSocket->set([
            'packer_class' => FramePacker::class,
        ]);
        $this->asyncSocket->on(AsyncSocketInterface::ON_MESSAGE, function (mixed $data) {
            $result = Result::parse($data);
            if (!$result->success || !is_array($result->data) || !isset($result->data['type'])) {
                return;
            }
            switch ($result->data['type']) {
                case 'message':
                    if (isset($this->callbacks['message'][$result->data['channel']])) {
                        call_user_func($this->callbacks['message'][$result->data['channel']], $result->data);
                    }
                    break;
                case 'channels':
                    if (isset($this->callbacks['channels'])) {
                        foreach ($this->callbacks['channels'] as $callback) {
                            call_user_func($callback, $result->data);
                        }
                    }
                    break;
            }
        });
    }

    /**
     * @param string|array $channels
     * @param callable $callback
     * @return bool
     */
    public function subscribe(string|array $channels, callable $callback): bool
    {
        $channels = (array)$channels;
        foreach ($channels as $channel) {
            $this->callbacks['message'][$channel] = $callback;
        }
        return $this->conn->sendCommand('channel:subscribe', $channels);
    }

    /**
     * @param string|array $channels
     * @return bool
     */
    public function unsubscribe(string|array $channels): bool
    {
        return $this->conn->sendCommand('channel:unsubscribe', $channels);
    }

    /**
     * @param string|array $channels
     * @param string $message
     * @return bool
     */
    public function publish(string|array $channels, string $message): bool
    {
        return $this->conn->publish($channels, $message);
    }

    /**
     * @param callable $callback
     * @return bool
     */
    public function channels(callable $callback): bool
    {
        $this->callbacks[__FUNCTION__][] = $callback;
        return $this->conn->sendCommand('channel:channels');
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        return $this->conn->sendCommand('channel:close');
    }
}