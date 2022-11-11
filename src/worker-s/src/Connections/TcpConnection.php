<?php

declare(strict_types=1);

namespace Larmias\WorkerS\Connections;

use Larmias\WorkerS\Protocols\Http\Request;
use Larmias\WorkerS\Server;
use Larmias\WorkerS\Manager;
use RuntimeException;
use Stringable;
use Larmias\WorkerS\Constants\Event as EventConstant;

class TcpConnection extends Connection
{
    /**
     * @var int
     */
    public const STATUS_CONNECTING = 1;

    /**
     * @var int
     */
    public const STATUS_CLOSED = 2;

    /**
     * @var int
     */
    public const READ_BUFFER_SIZE = 87380;

    /**
     * @var Server
     */
    protected Server $server;

    /**
     * @var resource
     */
    protected $socket;

    /**
     * @var string
     */
    protected string $remoteAddr;

    /**
     * @var array
     */
    public static array $connections = [];

    /**
     * @var integer
     */
    protected int $id;

    /**
     * @var integer
     */
    protected int $status;

    /**
     * @var integer
     */
    protected int $currentPackageLen = 0;

    /**
     * @var integer
     */
    public int $maxPackageSize = 1048576;

    /**
     * 接收数据缓冲区
     *
     * @var string
     */
    protected string $recvBuffer = '';

    /**
     * 当前连接已接收的字节数大小
     * 
     * @var integer
     */
    protected int $recvLen = 0;

    /**
     * 发送数据缓冲区
     *
     * @var string
     */
    protected string $sendBuffer = '';

    /**
     * 发送的数据字节数大小
     *
     * @var integer
     */
    protected int $sendLen = 0;

    /**
     * ProtocolInterface class
     * 
     * @var string
     */
    protected string $protocol;

    /**
     * @var Request
     */
    public Request $request;

    /**
     * TcpConnection __construct.
     *
     * @param Server   $server
     * @param resource $socket
     * @param string   $remoteAddr
     */
    public function __construct(Server $server,$socket,string $remoteAddr = '')
    {
        $this->server     = $server;
        $this->socket     = $socket;
        $this->remoteAddr = $remoteAddr;
        $this->status     = self::STATUS_CONNECTING;
        $this->protocol   = $this->server->getProtocol();

        if (!\is_resource($this->socket)) {
            throw new RuntimeException('Is not a valid socket connection');
        }

        $this->id = (int)$this->socket;
        static::$connections[$this->id] = $this->socket;
        
        \stream_set_blocking($this->socket,false);
        \stream_set_read_buffer($this->socket,0);

        $this->server->getEvent()->onReadable($this->socket,[$this,'onRead']);

        static::$statistics['connection_count']++;
    }

    /**
     * @return void
     */
    public function onRead()
    {
        $buffer = '';
        try {
            $buffer = \fread($this->socket, self::READ_BUFFER_SIZE);
        } catch (\Throwable $e) {
        }

        if ($buffer === '' || $buffer === false || !$this->isConnected()) {
            $this->close();
            return;
        }

        $this->recvLen    += \strlen($buffer);
        $this->recvBuffer .= $buffer;

        if ($this->protocol !== null) {
            $this->handleProtocolMessage();
            return;
        }

        if ($this->recvBuffer === '') {
            return;
        }
        
        $this->runEvent($this->recvBuffer);
        $this->recvBuffer = '';
    }

    /**
     * @return void
     */
    public function handleProtocolMessage(): void
    {
        while ($this->recvBuffer !== '') {
            $recvBuffeLen = \strlen($this->recvBuffer);
            if ($this->currentPackageLen) {
                if ($this->currentPackageLen > $recvBuffeLen) {
                    break;
                }
            } else {
                $this->currentPackageLen = $this->protocol::input($this->recvBuffer,$this);
                if ($this->currentPackageLen === 0) {
                    break;
                } else if ($this->currentPackageLen > 0 && $this->currentPackageLen <= $this->maxPackageSize) {
                    if ($this->currentPackageLen  > $recvBuffeLen) {
                        break;
                    }
                } else {
                    Manager::trace('Error package length = ' . $this->currentPackageLen);
                    $this->close();
                    return;
                }
            }            
            $message = \substr($this->recvBuffer,0,$this->currentPackageLen);
            $this->recvBuffer = \substr($this->recvBuffer,$this->currentPackageLen);
            $this->currentPackageLen = 0;
            $data = $this->protocol::decode($message,$this);
            $this->runEvent($data);
        }
    }

    /**
     * @param mixed $message
     * @return void
     */
    public function runEvent($message): void
    {
        static::$statistics['total_request']++;
        switch ($this->server->getScheme()) {
            case 'http':
                $this->server->fireEvent(EventConstant::ON_REQUEST,$message,$this->protocol::createResponse($this));
                break;
            case 'websocket':
                if (!isset($this->webSocketHandShake)) {
                    $this->request = $message;
                    if ($this->send()) {
                        $this->webSocketHandShake = true;
                        $this->server->fireEvent(EventConstant::ON_OPEN,$this);
                    } else {
                        $this->close();
                    }
                } else {
                    $this->server->fireEvent(EventConstant::ON_MESSAGE,$this,$message);
                }
                break;
            default:
                $this->server->fireEvent(EventConstant::ON_RECEIVE,$this,$message);
        }
    }

    /**
     * @return int
     */
    public function onWrite()
    {
        if (!$this->isConnected()) {
            $this->close();
            return 0;
        }

        $len = 0;
        try {
            $len = \fwrite($this->socket, $this->sendBuffer);
        } catch (\Throwable $e) {
        }
        $dataLen = strlen($this->sendBuffer);
        if ($len === $dataLen) {
            $this->server->getEvent()->offWritable($this->socket);
            $this->sendBuffer = '';
            $this->sendLen += $len;
            static::$statistics['total_response']++;
            return 1;
        }
        if ($len > 0) {
            $this->sendBuffer = \substr($this->sendBuffer, $len);
            $this->sendLen += $len;
            static::$statistics['total_response']++;
            return -1;
        }
        $this->close();
        return 0;
    }

    /**
     * 发送数据
     *
     * @param  string|Stringable $data
     * @param  bool $isRaw
     * @return int
     */
    public function send(string|Stringable $data = '',bool $isRaw = false): int
    {
        if (!$this->isConnected()) {
            $this->close();
            return 0;
        }

        if ($this->protocol !== null && !$isRaw) {
            $data = $this->protocol::encode((string)$data,$this);
            if ($data === '') {
                return 0;
            }
        }

        if ($this->sendBuffer === '') {
            $len = 0;
            $dataLen = \strlen($data);
            try {
                $len = \fwrite($this->socket, $data);
            } catch (\Throwable $e) {
            }
            if ($len === $dataLen) {
                $this->sendLen += $len;
                static::$statistics['total_response']++;
                return $len;
            }
            if ($len > 0) {
                $this->sendBuffer = \substr($data, $len);
                $this->sendLen += $len;
                static::$statistics['total_response']++;
            } else {
                if (!$this->isConnected()) {
                   try {
                        $this->server->fireEvent(EventConstant::ON_ERROR,$this,self::SEND_FAIL,'client closed');
                        static::$statistics['send_fail']++;
                    } catch (\Throwable $e) {
                        Manager::trace($e->getMessage());
                    }
                    $this->close();
                    return 0;
                }
                $this->sendBuffer = $data;
            }
            $this->server->getEvent()->onWritable($this->socket,[$this,'onWrite']);
            return -1;
        }
        $this->sendBuffer .= $data;
        return -1;
    }


    /**
     * 关闭连接.
     *
     * @param string  $data
     * @param boolean $isRaw
     * @return void
     */
    public function close(string|Stringable $data = null,bool $isRaw = false): void
    {
        if ($this->status === self::STATUS_CLOSED) {
            return;
        }

        if ($data !== null) {
            $this->send($data,$isRaw);
        }
        
        if (\is_resource($this->socket)) {
            $this->server->getEvent()->offReadable($this->socket);
            $this->server->getEvent()->offWritable($this->socket);
            try {
                \fclose($this->socket);
            } catch (\Throwable $e) {
            }
        }

        static::$statistics['connection_count']--;
        
        $this->status = self::STATUS_CLOSED;
        $this->server->fireEvent(EventConstant::ON_CLOSE,$this);

        $id = $this->getId();
        if ($this->server) {
            unset($this->server->connections[$id]);
        }
        unset(static::$connections[$id]);
    }

    /**
     * 是否连接.
     *
     * @return boolean
     */
    public function isConnected(): bool
    {
        return $this->status === self::STATUS_CONNECTING && !\feof($this->socket) && \is_resource($this->socket);
    }

    /**
     * @return resource
     */
    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getRemoteAddr(): string
    {
        return $this->remoteAddr;
    }

    /**
     * @return Server
     */
    public function getServer(): Server
    {
        return $this->server;
    }

    /**
     * TcpConnection __destruct.
     */
    public function __destruct()
    {
        $this->close();
    }
}
