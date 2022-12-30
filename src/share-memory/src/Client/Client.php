<?php

declare(strict_types=1);

namespace Larmias\ShareMemory\Client;

use Larmias\Engine\Timer;
use Larmias\ShareMemory\Client\Command\Channel;
use Larmias\ShareMemory\Exceptions\ClientException;
use Larmias\ShareMemory\Message\Command;
use Larmias\ShareMemory\Message\Result;

/**
 * Class Client
 * @package Larmias\ShareMemory
 */
class Client
{
    /**
     * @var resource
     */
    protected $socket;

    protected array $options = [
        'timeout' => 5,
        'ping_interval' => 30000,
    ];

    protected bool $connected = false;

    protected array $commands = [
        'channel' => Channel::class,
    ];

    public function __construct(protected string $host = '127.0.0.1', protected int $port = 2000, array $options = [])
    {
        $this->options = \array_merge($this->options, $options);
        $this->connect();
        $this->ping();
    }

    public function __get(string $name)
    {
        if (isset($this->commands[$name])) {
            return new $this->commands[$name]($this);
        }
        return null;
    }

    public function connect(): bool
    {
        if (!$this->isConnected()) {
            $conn = \stream_socket_client(
                \sprintf('tcp://%s:%d', $this->host, $this->port), $errCode, $errMsg,
                $this->options['timeout']
            );
            if (!\is_resource($conn)) {
                throw new ClientException($errMsg, $errCode);
            }
            $this->socket = $conn;
            $this->connected = true;
        }

        return $this->connected;
    }

    public function auth(string $password): bool
    {
        $result = $this->command(__FUNCTION__, [$password]);
        return $result && $result->success;
    }

    public function select(string $name): bool
    {
        $result = $this->command(__FUNCTION__, [$name]);
        return $result && $result->success;
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

    protected function ping(): void
    {
        if (!$this->options['ping_interval']) {
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