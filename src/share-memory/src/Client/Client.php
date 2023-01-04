<?php

declare(strict_types=1);

namespace Larmias\ShareMemory\Client;

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
     * @var resource
     */
    protected $socket;

    protected array $options = [
        'ping_interval' => 30000,
        'auto_connect' => true,
        'password' => '',
        'select' => 'default',
        'timeout' => 5,
    ];

    protected bool $connected = false;

    protected array $commands = [
        'channel' => Channel::class,
    ];

    protected array $container = [];

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
    }

    public function connect(): bool
    {
        if (!$this->isConnected()) {
            $this->socket = $this->createSocket();
            $this->connected = true;
            $this->ping();
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
        $result = $this->send(Command::build($name, $args));
        if (!$result) {
            return null;
        }
        return $this->read();
    }

    public function send(string $data): bool
    {
        if (!$this->isConnected()) {
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

    public function close(): bool
    {
        if ($this->isConnected()) {
            \fclose($this->socket);
        }
        return $this->connected = false;
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
        $this->options['ping_interval_id'] = Timer::tick($this->options['ping_interval'], function () {
            if (!$this->isConnected()) {
                return;
            }
            $this->command(Command::COMMAND_PING);
        });
    }

    public function __destruct()
    {
        if (isset($this->options['ping_interval_id'])) {
            Timer::del($this->options['ping_interval_id']);
        }
        $this->close();
    }
}