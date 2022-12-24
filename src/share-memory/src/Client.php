<?php

declare(strict_types=1);

namespace Larmias\ShareMemory;

use Larmias\ShareMemory\Exceptions\ClientException;
use Larmias\ShareMemory\Message\Command;

class Client
{
    /**
     * @var resource
     */
    protected $socket;

    protected array $options = [
        'timeout' => 5,
    ];

    protected bool $connected = false;

    public function __construct(protected string $host = '127.0.0.1', protected int $port = 2000, array $options = [])
    {
        $this->options = \array_merge($this->options, $options);
        $this->connect();
    }

    public function connect(): bool
    {
        if (!$this->isConnected()) {
            $conn = \stream_socket_client(
                sprintf('tcp://%s:%d', $this->host, $this->port), $errCode, $errMsg,
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

    public function command(string $name, array $args)
    {
        $result = $this->send(Command::build($name, $args));
        return $result;
    }

    public function send(string $data): bool
    {
        if (!$this->isConnected()) {
            return false;
        }
        $len = \strlen($data) + 4;
        $data = \pack('N', $len) . $data;
        $result = \fwrite($this->socket, $data, $len);
        if ($result === false) {
            $this->close();
        }
        return $result === $len;
    }

    public function close(): bool
    {
        if ($this->isConnected()) {
            \fclose($this->socket);
            $this->connected = false;
        }
        return $this->connected;
    }

    public function isConnected(): bool
    {
        return $this->connected && !\feof($this->socket) && \is_resource($this->socket);
    }
}