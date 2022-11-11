<?php

declare(strict_types=1);

namespace Larmias\WorkerS\Task\Channels;

use Larmias\WorkerS\Task\Channel;
use Larmias\WorkerS\Task\Contracts\RWEventInterface;
use RuntimeException;
use Socket;

class UnixSocket extends Channel implements RWEventInterface
{
    /**
     * @var string
     */
    const SUFFIX = '.sock';

    /**
     * @var int
     */
    const MAX_PACKAGE_SIZE = 65535;

    /**
     * @var string
     */
    protected string $socketFile;

    /**
     * @var Socket
     */
    protected Socket $unixSocket;

    /**
     * @var array
     */
    protected static array $unixSocketMap = [];

    /**
     * @var array
     */
    protected array $config = [
        'path' => null,
    ];

    /**
     * @var boolean
     */
    protected bool $isClose = false;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = \array_merge($this->config,$config);
    }

    /**
     * 初始化
     *
     * @return void
     */
    public function init(): void
    {
        if (is_null($this->config['path'])) {
            $this->config['path'] = \sys_get_temp_dir();
        }
        $this->socketFile = \rtrim($this->config['path'],\DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR . $this->taskWorker->getKey() . self::SUFFIX;
        if (isset(static::$unixSocketMap[$this->socketFile])) {
            $this->unixSocket = static::$unixSocketMap[$this->socketFile];
            return;
        }

        if (\file_exists($this->socketFile)) {
            \unlink($this->socketFile);
        }
        
        $this->unixSocket = \socket_create(\AF_UNIX,\SOCK_DGRAM,0);
        if (!$this->unixSocket) {
            throw new RuntimeException("unix socket create fail");
        }
        \socket_bind($this->unixSocket,$this->socketFile);
        // 设置非堵塞
        \socket_set_nonblock($this->unixSocket);

        static::$unixSocketMap[$this->socketFile] = $this->unixSocket;
    }
    
    /**
     * @param  string  $raw
     * @return int|null
     */
    public function push(string $raw): ?int
    {
        $sendLen = \socket_sendto($this->unixSocket,$raw,\strlen($raw),0,$this->socketFile);
        return $sendLen === false ? null : $sendLen;
    }

    /**
     * @return void
     */
    public function onReadable(): void
    {
        if ($this->isClose) {
            return;
        }
        $len = \socket_recvfrom($this->unixSocket,$data,self::MAX_PACKAGE_SIZE,0,$this->socketFile);
        if (!$len) {
            return;
        }
        $this->taskWorker->runTask($data);
    }

    /**
     * @return void
     */
    public function onWritable(): void
    {
    }

    /**
     * @return resource
     */
    public function getStream()
    {
        return \socket_export_stream($this->unixSocket);
    }

    /**
     * @return string|null
     */
    public function shift(): ?string
    {
        return null;
    }

    /**
     * @return boolean
     */
    public function clear(): bool
    {
        return true;
    }

    /**
     * @return boolean
     */
    public function close(): bool
    {
        $this->isClose = true;
        \socket_close($this->unixSocket);
        return true;
    }
}