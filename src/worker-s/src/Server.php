<?php

declare(strict_types=1);

namespace Larmias\WorkerS;

use Larmias\WorkerS\Events\EventInterface;
use Larmias\WorkerS\Connections\{Connection,TcpConnection};
use Larmias\WorkerS\Concerts\HasEvents;
use Larmias\WorkerS\Task\TaskWorker;
use Larmias\WorkerS\Process\Worker\Worker;
use Larmias\WorkerS\Support\Helper;
use Larmias\Utils\Arr;
use RuntimeException;

class Server
{
    use HasEvents;

    /**
     * @var string
     */
    const VERSION = '1.0.0';

    /**
     * @var string
     */
    protected string $serverId;

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
     * @var array
     */
    protected array $config = [];

    /**
     * @var EventInterface
     */
    protected EventInterface $event;

    /**
     * ProtocolInterface class
     * 
     * @var string|null
     */
    protected ?string $protocol;

    /**
     * server name.
     *
     * @var string
     */
    protected string $name;

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
     * @var TaskWorker
     */
    protected TaskWorker $taskWorker;

    /**
     * Server __construct.
     *
     * @param ?string $address
     */
    public function __construct(?string $address = null)
    {
        $parseResult = $address ? \parse_url($address) : [];

        if ($parseResult === false || \is_null($parseResult)) {
            throw new RuntimeException(
                \sprintf('%s Is not a valid address.',$address)
            );
        }
        $this->scheme      = $parseResult['scheme'] ?? 'tcp';
        $this->bindIp      = $parseResult['host'] ?? '0.0.0.0';
        $this->bindPort    = $parseResult['port'] ?? 9863;
        $this->transport   = 'tcp';
        $this->name        = $this->scheme;
        if (!isset(static::$supportProtocols[$this->scheme])) {
            throw new RuntimeException(
                \sprintf('Unsupported protocol: %s',$this->scheme)
            );
        }

        $this->protocol = static::$supportProtocols[$this->scheme] ?: null;
        $this->serverId = \spl_object_hash($this);
        
        WorkerS::addServer($this);
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
     * @return void
     */
    public function start(): void
    {
        WorkerS::runAll($this->serverId);
    }

    /**
     * @param Worker $worker
     * @return void
     */
    public function workerStart(Worker $worker,EventInterface $event)
    {
        $this->event = $event;
        Helper::setProcessTitle(WorkerS::getProcessTitle($this->name . ' server process'));
        $this->fireEvent('workerStart',$worker);
    }

    /**
     * @return void
     */
    public function listen()
    {
        // bind
        $this->config['reuse_port'] && $this->bind();
        // event listen
        $this->event->onReadable($this->mainSocket,[$this,'resumeAccept']);
        // event loop
        $this->event->run();
    }

    /**
     * @return void
     */
    public function workerStop(Worker $worker)
    {
        $this->fireEvent('workerStop',$worker);
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
        
        $this->mainSocket = \stream_socket_server($this->getServerAddress(),$errNo,$errMsg,$flags,$this->context);
        if (!\is_resource($this->mainSocket)) {
            throw new RuntimeException(
                \sprintf('stream socket server create fail %s<%d>',$errMsg,$errNo)
            );
        }
        // 设置非堵塞
        \stream_set_blocking($this->mainSocket,false);
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
        \set_error_handler(function(){});
        $newSocket = \stream_socket_accept($socket,0,$remoteAddr);
        \restore_error_handler();
        if (!\is_resource($newSocket)) {
            return;
        }
        $connection = new TcpConnection($this,$newSocket,$remoteAddr);
        $this->connections[$connection->getId()] = $connection;
        $this->fireEvent('connect',$connection);
    }

    /**
     * 投递task
     *
     * @param  callable $callback
     * @param  array    $args
     * @return bool
     */
    public function task(callable $callback,array $args = []): bool
    {
        if ($this->config['task_worker_num'] <= 0) {
            return false;
        }
        return $this->taskWorker->task($callback,$args);
    }

    /**
     * 初始化配置
     *
     * @return void
     */
    public function initConfig(): void
    {
        $this->config = \array_merge(static::getDefaultConfig(),$this->config);
        $this->config['task_worker_num'] = $this->config['task_worker_num'] ?? 0;
        $this->config['worker_num']      = max(1,$this->config['worker_num'] ?? 1);
    }

    /**
     * 设置配置.
     *
     * @param string|array $name
     * @param mixed $value
     * @return self
     */
    public function setConfig(string|array $name,$value = null): self
    {
        if (is_array($name)) {
            $this->config = \array_merge($this->config,$name);
        } else {
            Arr::set($this->config,$name,$value);
        }
        return $this;
    }

    /**
     * 获取配置
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getConfig(string $name,mixed $default = null): mixed
    {
        return Arr::get($this->config,$name,$default);
    }

    /**
     * @return string
     */
    protected function getServerAddress(): string
    {
        return \sprintf('%s://%s:%d',$this->transport,$this->bindIp,$this->bindPort);
    }

    /**
     * @return EventInterface
     */
    public function getEvent(): EventInterface
    {
        return $this->event;
    }

    /**
     * @return string|null
     */
    public function getProtocol(): ?string
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
     * @param TaskWorker $taskWorker
     * @return self
     */
    public function setTaskWorker(TaskWorker $taskWorker): self
    {
        $this->taskWorker = $taskWorker;
        return $this;
    }

    /**
     * @return string
     */
    public function getServerId(): string
    {
        return $this->serverId;
    }

    /**
     * @return array
     */
    public static function getDefaultConfig(): array
    {
        return [
            'worker_num'      => 1,
            'task_worker_num' => 0,
            'context_option'  => [
                'socket' => [
                    'backlog' => 102400,
                ]
            ],
            'reuse_port'  => true,
            'task'        => [],
        ];
    }

    /**
     * Get server name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
    /**
     * Set server name.
     *
     * @param  string  $name  server name.
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
}
