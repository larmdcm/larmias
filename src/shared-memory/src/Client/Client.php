<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Client;

use Larmias\Contracts\EventLoopInterface;
use Larmias\Contracts\TimerInterface;
use Larmias\SharedMemory\Client\Command\Channel;
use Larmias\SharedMemory\Client\Command\Str;
use Larmias\SharedMemory\Exceptions\ClientException;
use Larmias\SharedMemory\Message\Command;
use Larmias\SharedMemory\Message\Result;
use function array_merge;
use function stream_set_blocking;
use function stream_set_write_buffer;
use function stream_set_read_buffer;
use function stream_socket_client;
use function strlen;
use function pack;
use function fwrite;
use function fread;
use function unpack;
use function is_resource;
use function sprintf;
use function fclose;
use function feof;
use function call_user_func_array;
use const PHP_SAPI;

/**
 * @property Str $str
 * @property Channel $channel
 */
class Client
{
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
     * @var resource
     */
    protected $socket;

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
        'timeout' => 5,
        'event' => [],
        'async' => false,
    ];

    /**
     * @var boolean
     */
    protected bool $connected = false;

    /**
     * @var array
     */
    protected array $commands = [
        'map' => Str::class,
        'channel' => Channel::class,
    ];

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
    protected ?EventLoopInterface $eventLoop = null;

    /**
     * @var TimerInterface|null
     */
    protected ?TimerInterface $timer = null;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
        foreach ($this->options['event'] as $event => $callback) {
            $this->on($event, $callback);
        }
        if ($this->options['auto_connect']) {
            $this->connect();
        }
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function __get(string $name)
    {
        if (isset($this->commands[$name])) {
            if (!isset($this->container[$name])) {
                $this->container[$name] = new $this->commands[$name]($this);
            }
            return $this->container[$name];
        }
        return null;
    }

    /**
     * @return bool
     */
    public function connect(): bool
    {
        if (!$this->isConnected()) {
            $this->socket = $this->createSocket();
            $this->connected = true;
            if ($this->options['password'] !== '') {
                $this->auth($this->options['password']);
            }
            if ($this->options['select'] !== 'default') {
                $this->select($this->options['select']);
            }
            if ($this->options['async']) {
                stream_set_blocking($this->socket, false);
                stream_set_write_buffer($this->socket, 0);
                stream_set_read_buffer($this->socket, 0);
            }
            $this->ping();
            $this->trigger(self::EVENT_CONNECT, $this);
        }
        return $this->connected;
    }

    /**
     * @param array $options
     * @return self
     */
    public function set(array $options = []): self
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
        $result = fwrite($this->socket, $data, $len);
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

        $buffer = fread($this->socket, $protocolLen);
        if ($buffer === '' || $buffer === false) {
            return null;
        }
        $length = unpack('Nlength', $buffer)['length'];
        $buffer = fread($this->socket, $length - $protocolLen);

        return Result::parse($buffer);
    }

    /**
     * @param bool $destroy
     * @return bool
     */
    public function close(bool $destroy = false): bool
    {
        $this->clearPing();
        if ($this->isConnected()) {
            if (is_resource($this->socket)) {
                if ($this->eventLoop) {
                    $this->eventLoop->offReadable($this->socket);
                    $this->eventLoop->offWritable($this->socket);
                }
                fclose($this->socket);
            }
            $this->trigger(self::EVENT_CLOSE, $this);
            if (!$destroy && $this->options['break_reconnect']) {
                $this->reconnect();
            }
        }
        $this->connected = false;
        return true;
    }

    /**
     * @return self
     */
    public function reconnect(): self
    {
        if ($this->close(true)) {
            $this->connect();
            $this->trigger(self::EVENT_RECONNECT, $this);
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->connected && !feof($this->socket) && is_resource($this->socket);
    }

    /**
     * @return bool
     */
    public function isCli(): bool
    {
        return PHP_SAPI === 'cli';
    }

    /**
     * @return resource
     */
    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * @param string $event
     * @param callable $callback
     * @return self
     */
    public function on(string $event, callable $callback): self
    {
        $this->events[$event][] = $callback;
        return $this;
    }

    /**
     * @param string $event
     * @param ...$args
     * @return void
     */
    public function trigger(string $event, ...$args): void
    {
        if (isset($this->events[$event])) {
            foreach ($this->events[$event] as $callback) {
                call_user_func_array($callback, $args);
            }
        }
    }

    /**
     * @return resource
     */
    protected function createSocket()
    {
        $conn = stream_socket_client(
            sprintf('tcp://%s:%d', $this->options['host'], $this->options['port']), $errCode, $errMsg,
            $this->options['timeout']
        );
        if (!is_resource($conn)) {
            throw new ClientException($errMsg, $errCode);
        }
        return $conn;
    }

    /**
     * @return void
     */
    protected function ping(): void
    {
        if (!$this->options['ping_interval'] || !$this->isCli() || !$this->timer) {
            return;
        }
        $this->clearPing();
        $this->options['ping_interval_id'] = $this->timer->tick($this->options['ping_interval'], function () {
            if (!$this->isConnected()) {
                $this->close();
                return;
            }
            $this->sendCommand(Command::COMMAND_PING);
        });
    }

    /**
     * @return void
     */
    protected function clearPing(): void
    {
        if (isset($this->options['ping_interval_id']) && $this->timer) {
            $this->timer->del($this->options['ping_interval_id']);
        }
    }

    /**
     * @return EventLoopInterface|null
     */
    public function getEventLoop(): ?EventLoopInterface
    {
        return $this->eventLoop;
    }

    /**
     * @param EventLoopInterface|null $eventLoop
     */
    public function setEventLoop(?EventLoopInterface $eventLoop): void
    {
        $this->eventLoop = $eventLoop;
    }

    /**
     * @return TimerInterface|null
     */
    public function getTimer(): ?TimerInterface
    {
        return $this->timer;
    }

    /**
     * @param TimerInterface|null $timer
     */
    public function setTimer(?TimerInterface $timer): void
    {
        $this->timer = $timer;
    }

    public function __destruct()
    {
        $this->close(true);
    }
}