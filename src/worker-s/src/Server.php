<?php

declare(strict_types=1);

namespace Larmias\WorkerS;

use Larmias\WorkerS\Events\EventInterface;
use Larmias\WorkerS\Connections\{Connection, TcpConnection};
use Larmias\WorkerS\Process\Worker\Worker as ProcessWorker;
use Larmias\WorkerS\Support\Helper;
use Larmias\WorkerS\Worker as BaseWorker;
use Larmias\WorkerS\Constants\Event as EventConstant;
use RuntimeException;

class Server extends BaseWorker
{
    /**
     * stream resource
     *
     * @var resource
     */
    protected $mainSocket;

    /**
     * stream-context resource
     *
     * @var resource
     */
    protected $context;

    /**
     * @var Connection[]
     */
    public array $connections = [];

    /**
     * @var string
     */
    protected string $scheme;

    /**
     * @var string
     */
    protected string $transport;

    /**
     * @var string
     */
    protected string $bindIp;

    /**
     * @var int
     */
    protected int $bindPort;

    /**
     * @var EventInterface
     */
    protected EventInterface $event;

    /**
     * ProtocolInterface class
     *
     * @var string
     */
    protected string $protocol;

    /**
     * @var array
     */
    protected static array $supportProtocols = [
        'tcp'       => '',
        'frame'     => Protocols\Frame::class,
        'text'      => Protocols\Text::class,
        'http'      => Protocols\Http::class,
        'websocket' => Protocols\WebSocket::class,
    ];

    /**
     * Server __construct.
     *
     * @param ?string $address
     */
    public function __construct(?string $address = null)
    {
        parent::__construct();

        $parseResult = $address ? \parse_url($address) : [];

        if ($parseResult === false || \is_null($parseResult)) {
            throw new RuntimeException(
                \sprintf('%s Is not a valid address.', $address)
            );
        }
        $this->scheme    = $parseResult['scheme'] ?? 'tcp';
        $this->bindIp    = $parseResult['host'] ?? '0.0.0.0';
        $this->bindPort  = $parseResult['port'] ?? 9863;
        $this->transport = 'tcp';
        $this->name = $this->scheme;
        if (!isset(static::$supportProtocols[$this->scheme])) {
            throw new RuntimeException(
                \sprintf('Unsupported protocol: %s', $this->scheme)
            );
        }

        $this->protocol = static::$supportProtocols[$this->scheme];
    }

    /**
     * @return self
     */
    public function init(): self
    {
        $this->context = \stream_context_create($this->getConfig('context_option'));
        !$this->config['reuse_port'] && $this->bind();
        return $this;
    }

    /**
     * @param ProcessWorker $worker
     * @return void
     */
    public function workerStart(ProcessWorker $worker, EventInterface $event): void
    {
        $this->event = $event;
        Helper::setProcessTitle(Manager::getProcessTitle($this->name . ' server process'));
        $this->fireEvent(EventConstant::ON_WORKER_START, $worker);
    }

    /**
     * @return void
     */
    public function listen(): void
    {
        // bind
        $this->config['reuse_port'] && $this->bind();
        // event listen
        $this->event->onReadable($this->mainSocket, [$this, 'resumeAccept']);
        // event loop
        $this->event->run();
    }

    /**
     * @param ProcessWorker $worker
     * @return void
     */
    public function workerStop(ProcessWorker $worker): void
    {
        $this->fireEvent(EventConstant::ON_WORKER_STOP, $worker);
        $this->event->offReadable($this->mainSocket);
        foreach ($this->connections as $connection) {
            $connection->close();
        }
        \fclose($this->mainSocket);
        $this->event->stop();
    }

    /**
     * @return void
     */
    public function bind(): void
    {
        $flags = $this->transport === 'udp' ? \STREAM_SERVER_BIND : \STREAM_SERVER_BIND | \STREAM_SERVER_LISTEN;
        if ($this->getConfig('reuse_port')) {
            \stream_context_set_option($this->context, 'socket', 'so_reuseport', 1);
        }

        $this->mainSocket = \stream_socket_server($this->getServerAddress(), $errNo, $errMsg, $flags, $this->context);
        if (!\is_resource($this->mainSocket)) {
            throw new RuntimeException(
                \sprintf('stream socket server create fail %s<%d>', $errMsg, $errNo)
            );
        }
        // 设置非堵塞
        \stream_set_blocking($this->mainSocket, false);
    }

    /**
     * @return void
     */
    public function resumeAccept(): void
    {
        $this->acceptTcpConnection($this->mainSocket);
    }

    /**
     * @param resource $socket
     * @return void
     */
    protected function acceptTcpConnection($socket): void
    {
        try {
            $newSocket = \stream_socket_accept($socket, 0, $remoteAddr);
        } catch (\Throwable $e) {
            return;
        }
        if (!\is_resource($newSocket)) {
            return;
        }
        $connection = new TcpConnection($this, $newSocket, $remoteAddr);
        $this->connections[$connection->getId()] = $connection;
        $this->fireEvent(EventConstant::ON_CONNECT, $connection);
    }

    /**
     * 初始化配置
     *
     * @return void
     */
    public function initConfig(): void
    {
        parent::initConfig();
    }

    /**
     * @return string
     */
    protected function getServerAddress(): string
    {
        return \sprintf('%s://%s:%d', $this->transport, $this->bindIp, $this->bindPort);
    }

    /**
     * @return EventInterface
     */
    public function getEvent(): EventInterface
    {
        return $this->event;
    }

    /**
     * @return string
     */
    public function getProtocol(): string
    {
        return $this->protocol;
    }

    /**
     * @return string
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @return array
     */
    public static function getDefaultConfig(): array
    {
        return \array_merge(BaseWorker::getDefaultConfig(),[
            'context_option' => [
                'socket' => [
                    'backlog' => 102400,
                ]
            ],
            'reuse_port' => false,
            'task' => [],
        ]);
    }
}
