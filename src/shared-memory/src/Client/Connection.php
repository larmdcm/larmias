<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Client;

use Larmias\Client\TcpClient;
use Larmias\Codec\Protocol\FrameProtocol;
use Larmias\SharedMemory\Client\Command\Queue;
use Larmias\SharedMemory\Client\Command\Str;
use Larmias\SharedMemory\Client\Command\Channel;
use Larmias\SharedMemory\Message\Command;
use Larmias\SharedMemory\Message\Result;
use Throwable;

class Connection extends TcpClient
{
    use Str, Queue, Channel;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $defaultOptions = [
            'ping_interval' => 30000,
            'auto_connect' => true,
            'break_reconnect' => true,
            'password' => '',
            'select' => 'default',
            'protocol' => FrameProtocol::class,
        ];
        parent::__construct(array_merge($defaultOptions, $options));
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
        return (bool)$this->send(Command::build($name, $args));
    }

    /**
     * @return Result|null
     * @throws Throwable
     */
    public function read(): ?Result
    {
        $buffer = $this->recv();
        if (!$buffer) {
            return null;
        }

        return Result::parse($buffer);
    }

    /**
     * @return void
     */
    protected function connectAfter(): void
    {
        if ($this->options['password'] !== '') {
            $this->auth($this->options['password']);
        }
        
        if ($this->options['select'] !== 'default') {
            $this->select($this->options['select']);
        }
    }
}