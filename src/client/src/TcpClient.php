<?php

declare(strict_types=1);

namespace Larmias\Client;

use Larmias\Client\Traits\Protocol;
use Larmias\Contracts\Client\SocketInterface;
use Larmias\Contracts\EventLoopInterface;
use Larmias\Contracts\TimerInterface;
use Larmias\Support\Traits\HasEvents;
use Closure;
use SplQueue;
use const PHP_SAPI;

class TcpClient
{
    use HasEvents, Protocol;

    /**
     * @var array
     */
    protected array $options = [
        'host' => '127.0.0.1',
        'port' => 2000,
        'ping_interval' => 0,
        'ping_data' => 'PING',
        'pong_data' => 'PONG',
        'ping_handler' => null,
        'auto_connect' => false,
        'break_reconnect' => false,
        'timeout' => 3,
        'event' => [],
        'async' => false,
        'protocol' => null,
    ];

    /**
     * @var SplQueue
     */
    protected SplQueue $dataQueue;

    /**
     * @var SocketInterface
     */
    protected SocketInterface $socket;

    /**
     * @var EventLoopInterface|null
     */
    protected static ?EventLoopInterface $eventLoop = null;

    /**
     * @var TimerInterface|null
     */
    protected static ?TimerInterface $timer = null;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->socket = new Socket();
        $this->dataQueue = new SplQueue();
        $this->setOptions($options);
        foreach ($this->options['event'] as $event => $callback) {
            $this->on($event, $callback);
        }
        if ($this->options['auto_connect']) {
            $this->connect();
        }
    }

    /**
     * @return bool
     */
    public function connect(): bool
    {
        if (!$this->isConnected()) {
            if ($this->options['async']) {
                $this->socket->setOptions([
                    'blocking' => false,
                    'read_buffer_size' => 0,
                ]);
            }
            $this->socket->connect($this->options['host'], $this->options['port'], $this->options['timeout']);
            $this->fireEvent(Constants::EVENT_CONNECT, $this);
            $this->startPing();
        }
        return $this->isConnected();
    }

    /**
     * 发送数据
     * @param string $data
     * @return int|false
     */
    public function send(string $data): int|false
    {
        if (!$this->isConnected()) {
            $this->close();
            return false;
        }

        if ($this->protocol) {
            $data = $this->protocol->pack($data);
        }

        return $this->socket->send($data);
    }

    /**
     * @param int $length
     * @return mixed
     */
    public function recv(int $length = 65535): mixed
    {
        if (!$this->isConnected()) {
            $this->close();
            return false;
        }

        while (true) {
            if (!$this->dataQueue->isEmpty()) {
                break;
            }
            $data = $this->socket->recv($length);
            $this->protocolHandler->handle($data, function (mixed $packageData) {
                $this->dataQueue->enqueue($packageData);
            });
            if ($this->options['async']) {
                break;
            }
        }

        return $this->dataDequeue();
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        return $this->destroy(false);
    }

    /**
     * @param bool $destroy
     * @return bool
     */
    public function destroy(bool $destroy = true): bool
    {
        $this->clearPing();
        $this->socket->close();
        $this->fireEvent(Constants::EVENT_CLOSE, $this);
        if (!$destroy && $this->options['break_reconnect']) {
            $this->reconnect();
        }
        return true;
    }

    /**
     * @return bool
     */
    public function reconnect(): bool
    {
        if ($this->destroy()) {
            $result = $this->connect();
            $this->fireEvent(Constants::EVENT_RECONNECT, $this);
            return $result;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function ping(): bool
    {
        if ($this->options['ping_handler'] instanceof Closure) {
            return $this->options['ping_handler']($this);
        }

        $pingData = $this->options['ping_data'];
        if ($pingData instanceof Closure) {
            $pingData = $pingData($this);
        }

        $check = (bool)$this->send($pingData);
        if (!$check) {
            return false;
        }

        $pongData = $this->options['pong_data'];
        if ($pongData instanceof Closure) {
            $pongData = $pongData($this);
        }

        return $this->recv(strlen($pongData)) == $pongData;
    }

    /**
     * @param array $options
     * @return self
     */
    public function setOptions(array $options = []): self
    {
        $this->options = array_merge($this->options, $options);
        $this->initProtocolHandler($this->options);

        return $this;
    }

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->socket->isConnected();
    }

    /**
     * @return mixed
     */
    public function dataDequeue(): mixed
    {
        return $this->dataQueue->isEmpty() ? null : $this->dataQueue->dequeue();
    }

    /**
     * @return SocketInterface
     */
    public function getSocket(): SocketInterface
    {
        return $this->socket;
    }

    /**
     * @return void
     */
    protected function startPing(): void
    {
        if (!$this->options['ping_interval'] || !$this->isCli() || !static::$timer) {
            return;
        }
        $this->clearPing();
        $this->options['ping_interval_id'] = static::$timer->tick($this->options['ping_interval'], function () {
            $this->ping();
            if (!$this->isConnected()) {
                $this->close();
            }
        });
    }

    /**
     * @return void
     */
    protected function clearPing(): void
    {
        if (isset($this->options['ping_interval_id']) && static::$timer) {
            static::$timer->del($this->options['ping_interval_id']);
        }
    }

    /**
     * @return bool
     */
    protected function isCli(): bool
    {
        return PHP_SAPI === 'cli';
    }

    /**
     * @return EventLoopInterface|null
     */
    public static function getEventLoop(): ?EventLoopInterface
    {
        return static::$eventLoop;
    }

    /**
     * @param EventLoopInterface|null $eventLoop
     */
    public static function setEventLoop(?EventLoopInterface $eventLoop): void
    {
        static::$eventLoop = $eventLoop;
    }

    /**
     * @return TimerInterface|null
     */
    public static function getTimer(): ?TimerInterface
    {
        return static::$timer;
    }

    /**
     * @param TimerInterface|null $timer
     */
    public static function setTimer(?TimerInterface $timer): void
    {
        static::$timer = $timer;
    }

    public function __destruct()
    {
        $this->destroy();
    }
}