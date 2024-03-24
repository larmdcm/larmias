<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Client;

use Larmias\Client\Socket;
use Larmias\Contracts\Client\SocketInterface;
use Larmias\Contracts\EventLoopInterface;
use Larmias\Contracts\TimerInterface;
use Larmias\SharedMemory\Client\Command\Queue;
use Larmias\SharedMemory\Client\Command\Str;
use Larmias\SharedMemory\Client\Command\Channel;
use Larmias\SharedMemory\Message\Command;
use Larmias\SharedMemory\Message\Result;
use Larmias\Support\Traits\HasEvents;
use function array_merge;
use function strlen;
use function pack;
use function unpack;
use const PHP_SAPI;

class Connection
{
    use HasEvents, Str, Queue, Channel;

    /**
     * @var string
     */
    public const EVENT_CONNECT = 'connect';

    /**
     * @var string
     */
    public const EVENT_RECONNECT = 'reconnect';

    /**
     * @var string
     */
    public const EVENT_CLOSE = 'close';

    /**
     * @var array
     */
    protected array $options = [
        'host' => '127.0.0.1',
        'port' => 2000,
        'ping_interval' => 30000,
        'auto_connect' => true,
        'break_reconnect' => true,
        'password' => '',
        'select' => 'default',
        'timeout' => 3,
        'event' => [],
        'async' => false,
    ];

    /**
     * @var SocketInterface
     */
    protected SocketInterface $socket;

    /**
     * @var array
     */
    protected array $container = [];

    /**
     * @var array
     */
    protected array $events = [];

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
        $this->options = array_merge($this->options, $options);
        $this->socket = new Socket();
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
                $this->socket->set([
                    'blocking' => false,
                    'read_buffer_size' => 0,
                ]);
            }
            $this->socket->connect($this->options['host'], $this->options['port'], $this->options['timeout']);
            if ($this->options['password'] !== '') {
                $this->auth($this->options['password']);
            }
            if ($this->options['select'] !== 'default') {
                $this->select($this->options['select']);
            }
            $this->startPing();
            $this->fireEvent(self::EVENT_CONNECT, $this);
        }
        return $this->isConnected();
    }

    /**
     * @param array $options
     * @return self
     */
    public function setOptions(array $options = []): self
    {
        $this->options = array_merge($this->options, $options);
        return $this;
    }

    /**
     * @param array $options
     * @return static
     */
    public function clone(array $options = []): static
    {
        return new static(array_merge($this->options, $options));
    }

    /**
     * @param string $password
     * @return bool
     */
    public function auth(string $password): bool
    {
        $result = $this->command(__FUNCTION__, [$password]);
        if ($result && $result->success) {
            $this->options['password'] = $password;
            return true;
        }
        return false;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function select(string $name): bool
    {
        $result = $this->command(__FUNCTION__, [$name]);
        if ($result && $result->success) {
            $this->options['select'] = $name;
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function ping(): bool
    {
        return (bool)$this->command(Command::COMMAND_PING)?->success;
    }

    /**
     * @param string $name
     * @param array $args
     * @return Result|null
     */
    public function command(string $name, array $args = []): ?Result
    {
        $result = $this->sendCommand($name, $args);
        if (!$result) {
            return null;
        }
        return $this->read();
    }

    /**
     * @param string $name
     * @param array $args
     * @return bool
     */
    public function sendCommand(string $name, array $args = []): bool
    {
        return $this->send(Command::build($name, $args));
    }

    /**
     * @param string $data
     * @return bool
     */
    public function send(string $data): bool
    {
        if (!$this->isConnected()) {
            $this->close();
            return false;
        }
        $len = strlen($data) + 4;
        $data = pack('N', $len) . $data;
        $result = $this->socket->send($data);
        return $result === $len;
    }

    /**
     * @return Result|null
     */
    public function read(): ?Result
    {
        if (!$this->isConnected()) {
            $this->close();
            return null;
        }

        $protocolLen = 4;

        $buffer = $this->socket->recv($protocolLen);
        if ($buffer === '' || $buffer === false) {
            return null;
        }

        $length = unpack('Nlength', $buffer)['length'];
        $buffer = $this->socket->recv($length - $protocolLen);

        return Result::parse($buffer);
    }

    /**
     * @param bool $destroy
     * @return bool
     */
    public function close(bool $destroy = false): bool
    {
        $this->clearPing();
        $this->socket->close();
        $this->fireEvent(self::EVENT_CLOSE, $this);
        if (!$destroy && $this->options['break_reconnect']) {
            $this->reconnect();
        }
        return true;
    }

    /**
     * @return bool
     */
    public function destroy(): bool
    {
        return $this->close(true);
    }

    /**
     * @return bool
     */
    public function reconnect(): bool
    {
        if ($this->close(true)) {
            $result = $this->connect();
            $this->fireEvent(self::EVENT_RECONNECT, $this);
            return $result;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->socket->isConnected();
    }

    /**
     * @return bool
     */
    public function isCli(): bool
    {
        return PHP_SAPI === 'cli';
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
        $this->close(true);
    }
}