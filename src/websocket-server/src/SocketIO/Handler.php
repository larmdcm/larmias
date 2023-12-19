<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer\SocketIO;

use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\TimerInterface;
use Larmias\Contracts\WebSocket\FrameInterface;
use Larmias\WebSocketServer\AbstractHandler;
use Larmias\WebSocketServer\Contracts\EventInterface;
use Larmias\WebSocketServer\Contracts\HandlerInterface;
use Throwable;
use function json_encode;

class Handler extends AbstractHandler implements HandlerInterface
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
     * @var TimerInterface
     */
    protected TimerInterface $timer;

    /**
     * @var int
     */
    protected int $pingTimeoutTimer;

    /**
     * @var int
     */
    protected int $pingIntervalTimer;

    /**
     * 初始化
     * @param TimerInterface $timer
     * @param ConfigInterface|null $config
     * @return void
     */
    public function initialize(TimerInterface $timer, ?ConfigInterface $config = null): void
    {
        $this->timer = $timer;
        if ($config) {
            $this->config['ping_interval'] = $config->get('websocket.ping_interval', 25000);
            $this->config['ping_timeout'] = $config->get('websocket.ping_timeout', 60000);
        }
    }

    /**
     * 处理连接打开
     * @return void
     */
    public function open(): void
    {
        $this->eio = (int)$this->socket->getConnection()->getRequest()->query('EIO');
        $payload = json_encode([
            'sid' => $this->socket->getSid(),
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
     * @param FrameInterface $frame
     * @return void
     */
    public function message(FrameInterface $frame): void
    {
        $data = $frame->getData();
        $enginePacket = EnginePacket::fromString($data);
        $this->resetPingTimeout($this->config['ping_interval'] + $this->config['ping_timeout']);
        switch ($enginePacket->type) {
            case EnginePacket::MESSAGE:
                $this->handleMessage($frame, $enginePacket);
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
     * 处理消息
     * @param FrameInterface $frame
     * @param EnginePacket $enginePacket
     * @return void
     */
    protected function handleMessage(FrameInterface $frame, EnginePacket $enginePacket): void
    {
        $packet = Packet::fromString($enginePacket->data);
        switch ($packet->type) {
            case Packet::CONNECT:
                $this->onConnect(array_shift($packet->data));
                break;
            case Packet::EVENT:
                $type = array_shift($packet->data);
                $data = array_shift($packet->data);
                $result = $this->dispatch(Frame::from($frame, $data), fn() => $this->trigger($type, $data));
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
                $this->trigger(EventInterface::ON_DISCONNECT);
                $this->socket->close();
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
        $this->trigger(EventInterface::ON_CLOSE);
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
            $this->trigger(EventInterface::ON_CONNECT, $data);
            $packet = Packet::create(Packet::CONNECT);
            if ($this->eio >= 4) {
                $packet->data = ['sid' => $this->socket->getSid()];
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
}