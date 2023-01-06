<?php

declare(strict_types=1);

namespace Larmias\ShareMemory\Client;

use Larmias\Engine\EventLoop;
use Larmias\Engine\Timer;
use Larmias\ShareMemory\Client\Command\Channel;
use Larmias\ShareMemory\Exceptions\ClientException;
use Larmias\ShareMemory\Message\Command;
use Larmias\ShareMemory\Message\Result;

/**
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
    public const EVENT_CLOSE = 'close';

    /**
     * @var resource
     */
    protected $socket;

    protected array $options = [
        'ping_interval' => 30000,
        'auto_connect' => true,
        'break_reconnect' => false,
        'password' => '',
        'select' => 'default',
        'timeout' => 5,
        'event' => [],
    ];

    protected bool $connected = false;

    protected array $commands = [
        'channel' => Channel::class,
    ];

    protected array $container = [];

    protected array $events = [];

    public function __construct(protected string $host = '127.0.0.1', protected int $port = 2000, array $options = [])
    {
        $this->options = \array_merge($this->options, $options);
        $this->init();
    }


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

    public function init(): void
    {
        foreach ($this->options['event'] as $event => $callback) {
            $this->on($event, $callback);
        }

        if ($this->options['auto_connect']) {
            $this->connect();
        }

        if (!$this->isConnected()) {
            return;
        }

        if ($this->options['password'] !== '') {
            $this->auth($this->options['password']);
        }

        if ($this->options['select'] !== 'default') {
            $this->select($this->options['select']);
        }

        if ($this->options['auto_connect']) {
            $this->trigger(self::EVENT_CONNECT, $this);
        }
    }

    public function connect(): bool
    {
        if (!$this->isConnected()) {
            $this->socket = $this->createSocket();
            $this->connected = true;
            $this->ping();
            if (!$this->options['auto_connect']) {
                $this->trigger(self::EVENT_CONNECT, $this);
            }
        }
        return $this->connected;
    }

    public function clone(array $options = []): Client
    {
        return new static($this->host, $this->port, \array_merge($this->options, $options));
    }

    public function auth(string $password): bool
    {
        $result = $this->command(__FUNCTION__, [$password]);
        if ($result && $result->success) {
            $this->options['password'] = $password;
            return true;
        }
        return false;
    }

    public function select(string $name): bool
    {
        $result = $this->command(__FUNCTION__, [$name]);
        if ($result && $result->success) {
            $this->options['select'] = $name;
            return true;
        }
        return false;
    }

    public function command(string $name, array $args = []): ?Result
    {
        $result = $this->sendCommand($name, $args);
        if (!$result) {
            return null;
        }
        return $this->read();
    }

    public function sendCommand(string $name, array $args = []): bool
    {
        return $this->send(Command::build($name, $args));
    }

    public function send(string $data): bool
    {
        if (!$this->isConnected()) {
            $this->close();
            return false;
        }
        $len = \strlen($data) + 4;
        $data = \pack('N', $len) . $data;
        $result = \fwrite($this->socket, $data, $len);
        return $result === $len;
    }


    public function read(): ?Result
    {
        if (!$this->isConnected()) {
            $this->close();
            return null;
        }

        $protocolLen = 4;

        $buffer = \fread($this->socket, $protocolLen);
        if ($buffer === '' || $buffer === false) {
            return null;
        }
        $length = \unpack('Nlength', $buffer)['length'];
        $buffer = \fread($this->socket, $length - $protocolLen);

        return Result::parse($buffer);
    }

    public function close(bool $destroy = false): bool
    {
        $this->clearPing();
        if ($this->isConnected()) {
            if (\is_resource($this->socket)) {
                EventLoop::offReadable($this->socket);
                EventLoop::offWritable($this->socket);
                \fclose($this->socket);
            }
            $this->trigger(self::EVENT_CLOSE, $this);
            if (!$destroy && $this->options['break_reconnect']) {
                $this->reconnect();
            }
        }
        $this->connected = false;
        return true;
    }

    public function reconnect(): self
    {
        if ($this->close(true)) {
            $this->init();
        }
        return $this;
    }

    public function isConnected(): bool
    {
        return $this->connected && !\feof($this->socket) && \is_resource($this->socket);
    }

    public function isCli(): bool
    {
        return \PHP_SAPI === 'cli';
    }

    /**
     * @return resource
     */
    public function getSocket()
    {
        return $this->socket;
    }

    public function on(string $event, callable $callback): self
    {
        $this->events[$event][] = $callback;
        return $this;
    }

    public function trigger(string $event, ...$args): void
    {
        if (isset($this->events[$event])) {
            foreach ($this->events[$event] as $callback) {
                \call_user_func_array($callback, $args);
            }
        }
    }

    /**
     * @return resource
     */
    protected function createSocket()
    {
        $conn = \stream_socket_client(
            \sprintf('tcp://%s:%d', $this->host, $this->port), $errCode, $errMsg,
            $this->options['timeout']
        );
        if (!\is_resource($conn)) {
            throw new ClientException($errMsg, $errCode);
        }
        return $conn;
    }

    protected function ping(): void
    {
        if (!$this->options['ping_interval'] || !$this->isCli()) {
            return;
        }
        $this->clearPing();
        $this->options['ping_interval_id'] = Timer::tick($this->options['ping_interval'], function () {
            if (!$this->isConnected()) {
                $this->close();
                return;
            }
            $this->command(Command::COMMAND_PING);
        });
    }

    protected function clearPing()
    {
        if (isset($this->options['ping_interval_id'])) {
            Timer::del($this->options['ping_interval_id']);
        }
    }

    public function __destruct()
    {
        $this->close(true);
    }
}