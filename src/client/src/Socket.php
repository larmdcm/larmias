<?php

declare(strict_types=1);

namespace Larmias\Client;

use Larmias\Contracts\Client\ClientException;
use Larmias\Contracts\Client\SocketInterface;

class Socket implements SocketInterface
{
    /**
     * @var resource
     */
    protected mixed $socket;

    /**
     * @var bool
     */
    protected bool $connected = false;

    /**
     * @var array
     */
    protected array $options = [
        'rw_timeout' => 0,
        'blocking' => true,
        'read_buffer_size' => null,
        'write_buffer_size' => null,
        'context' => [],
    ];

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    /**
     * @param string $host
     * @param int $port
     * @param float $timeout
     * @return bool
     */
    public function connect(string $host, int $port, float $timeout = 0): bool
    {
        if (!$this->isConnected()) {
            $this->socket = $this->createSocket($host, $port, $timeout);
            $this->connected = true;
        }

        return $this->connected;
    }

    /**
     * @param array $options
     * @return SocketInterface
     */
    public function setOptions(array $options = []): SocketInterface
    {
        $this->options = array_merge($this->options, $options);
        return $this;
    }

    /**
     * @param mixed $data
     * @return int|false
     */
    public function send(mixed $data): int|false
    {
        return fwrite($this->socket, $data);
    }

    /**
     * @param int $length
     * @return string|false
     */
    public function recv(int $length = 65535): string|false
    {
        return fread($this->socket, $length);
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        if (!$this->connected) {
            return true;
        }

        if (is_resource($this->socket)) {
            fclose($this->socket);
        }

        $this->connected = false;

        return true;
    }

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->connected && is_resource($this->socket) && !feof($this->socket);
    }

    /**
     * @return resource
     */
    public function getSocket(): mixed
    {
        return $this->socket;
    }

    /**
     * 创建socket
     * @param string $host
     * @param int $port
     * @param float $timeout
     * @return resource
     */
    protected function createSocket(string $host, int $port, float $timeout = 0): mixed
    {
        $socket = function () use ($host, $port, $timeout) {
            set_error_handler(fn() => null);
            $context = null;
            if (!empty($this->options['context'])) {
                $context = stream_context_create($this->options['context']);
            }
            $conn = stream_socket_client(
                sprintf('tcp://%s:%d', $host, $port), $errCode, $errMsg,
                $timeout, STREAM_CLIENT_CONNECT, $context
            );
            restore_error_handler();
            if (!is_resource($conn)) {
                throw new ClientException($errMsg, $errCode);
            }

            stream_set_timeout($conn, $this->options['rw_timeout']);
            stream_set_blocking($conn, $this->options['blocking']);
            if (!is_null($this->options['read_buffer_size'])) {
                stream_set_read_buffer($conn, $this->options['read_buffer_size']);
            }
            if (!is_null($this->options['write_buffer_size'])) {
                stream_set_write_buffer($conn, $this->options['write_buffer_size']);
            }

            return $conn;
        };

        $beginTime = microtime(true);
        $tryTimeout = $timeout * 1000;

        while (true) {
            try {
                return $socket();
            } catch (ClientException $e) {
                if ($tryTimeout === 0 || microtime(true) - $beginTime > $tryTimeout / 1000) {
                    throw $e;
                }
                usleep(100000);
            }
        }
    }

    /**
     * @return int
     */
    public function getFd(): int
    {
        return (int)$this->socket;
    }
}