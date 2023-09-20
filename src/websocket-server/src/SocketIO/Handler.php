<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer\SocketIO;

use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\TimerInterface;
use Larmias\WebSocketServer\Contracts\EventInterface;
use Larmias\WebSocketServer\Contracts\HandlerInterface;
use Larmias\WebSocketServer\Socket;
use Throwable;
use function json_encode;
use function base64_encode;
use function uniqid;

class Handler implements HandlerInterface
{
    /**
     * @var array
     */
    protected array $config = [
        'ping_interval' => 25000,
        'ping_timeout' => 60000,
    ];

    /**
     * @var int
     */
    protected int $eio;

    /**
     * @var int
     */
    protected int $pingTimeoutTimer;

    /**
     * @var int
     */
    protected int $pingIntervalTimer;

    public function __construct(protected Socket $socket, protected TimerInterface $timer, protected EventInterface $event, ?ConfigInterface $config = null)
    {
        $this->config['ping_interval'] = $config->get('websocket.ping_interval', 25000);
        $this->config['ping_timeout'] = $config->get('websocket.ping_timeout', 60000);
    }

    /**
     * 处理连接打开
     * @return void
     */
    public function open(): void
    {
        $this->eio = (int)$this->socket->getConnection()->getRequest()->query('EIO');
        $payload = json_encode([
            'sid' => base64_encode(uniqid()),
            'upgrades' => [],
            'pingInterval' => $this->config['ping_interval'],
            'pingTimeout' => $this->config['ping_timeout'],
        ]);

        $this->push(EnginePacket::open($payload));

        if ($this->eio < 4) {
            $this->resetPingTimeout($this->config['ping_interval'] + $this->config['ping_timeout']);
            $this->onConnect();
        } else {
            $this->schedulePing();
        }
    }

    /**
     * 处理接受消息
     * @param mixed $data
     * @return void
     */
    public function message(mixed $data): void
    {
        $enginePacket = EnginePacket::fromString($data);
        $this->resetPingTimeout($this->config['ping_interval'] + $this->config['ping_timeout']);
        switch ($enginePacket->type) {
            case EnginePacket::MESSAGE:
                $packet = Packet::fromString($enginePacket->data);
                switch ($packet->type) {
                    case Packet::CONNECT:
                        $this->onConnect($packet->data);
                        break;
                    case Packet::EVENT:
                        $type = array_shift($packet->data);
                        $data = $packet->data;
                        $result = $this->event->trigger($type, $data);

                        if ($packet->id !== null) {
                            $responsePacket = Packet::create(Packet::ACK, [
                                'id' => $packet->id,
                                'nsp' => $packet->nsp,
                                'data' => $result,
                            ]);
                            $this->push($responsePacket);
                        }
                        break;
                    case Packet::DISCONNECT:
                        $this->event->trigger(EventInterface::ON_DISCONNECT);
                        $this->socket->close();
                        break;
                    default:
                        $this->socket->close();
                        break;
                }
                break;
            case EnginePacket::PING:
                $this->push(EnginePacket::pong($enginePacket->data));
                break;
            case EnginePacket::PONG:
                $this->schedulePing();
                break;
            default:
                $this->socket->close();
                break;
        }
    }

    /**
     * 处理连接关闭
     * @return void
     */
    public function close(): void
    {
        if (isset($this->pingTimeoutTimer)) {
            $this->timer->del($this->pingTimeoutTimer);
        }

        if (isset($this->pingIntervalTimer)) {
            $this->timer->del($this->pingIntervalTimer);
        }
    }

    /**
     * 处理新连接
     * @param mixed $data
     * @return void
     */
    protected function onConnect(mixed $data = null): void
    {
        try {
            $this->event->trigger(EventInterface::ON_CONNECT, $data);
            $packet = Packet::create(Packet::CONNECT);
            if ($this->eio >= 4) {
                $packet->data = ['sid' => base64_encode(uniqid())];
            }
        } catch (Throwable $e) {
            $packet = Packet::create(Packet::CONNECT_ERROR, [
                'data' => ['message' => $e->getMessage()],
            ]);
        }

        $this->push($packet);
    }

    /**
     * 重置超时PING
     * @param int $timeout
     * @return void
     */
    protected function resetPingTimeout(int $timeout): void
    {
        if (isset($this->pingTimeoutTimer)) {
            $this->timer->del($this->pingTimeoutTimer);
        }
        $this->pingTimeoutTimer = $this->timer->after($timeout, function () {
            $this->socket->close();
        });
    }

    /**
     * 调度PING
     * @return void
     */
    protected function schedulePing(): void
    {
        if (isset($this->pingIntervalTimer)) {
            $this->timer->del($this->pingIntervalTimer);
        }
        $this->pingIntervalTimer = $this->timer->after($this->config['ping_interval'], function () {
            $this->push(EnginePacket::ping());
            $this->resetPingTimeout($this->config['ping_timeout']);
        });
    }

    /**
     * 推送数据
     * @param mixed $data
     * @return void
     */
    public function push(mixed $data): void
    {
        $this->socket->push($data);
    }
}