<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Client\Command;

use Larmias\Client\AsyncSocket;
use Larmias\Client\Constants;
use Larmias\Contracts\Client\AsyncSocketInterface;
use Larmias\SharedMemory\Client\Connection;
use Larmias\SharedMemory\Message\Result;

abstract class AsyncCommand
{
    /**
     * @var array
     */
    protected array $callbacks = [];

    /**
     * @var AsyncSocketInterface
     */
    protected AsyncSocketInterface $asyncSocket;

    /**
     * @var Connection
     */
    protected Connection $conn;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $options['async'] = true;
        $options['event'] = array_merge($options['event'] ?? [], [
            Constants::EVENT_CONNECT => fn(Connection $conn) => $this->onConnect($conn)
        ]);
        $this->conn = new Connection($options);
    }

    /**
     * @param Connection $conn
     * @return void
     */
    protected function onConnect(Connection $conn): void
    {
        $options = $conn->getOptions();
        $this->asyncSocket = new AsyncSocket(Connection::getEventLoop(), $conn->getSocket());
        $this->asyncSocket->setOptions([
            'protocol' => $options['protocol'],
            'max_package_size' => $options['max_package_size'] ?? 0,
        ]);
        $this->asyncSocket->on(AsyncSocketInterface::ON_MESSAGE, function (mixed $data) {
            $result = Result::parse($data);
            if (!$result->success || !is_array($result->data) || !isset($result->data['type'])) {
                return;
            }
            $this->onMessage($result->data);
        });
    }

    /**
     * @param array $data
     * @return void
     */
    protected function onMessage(array $data): void
    {
    }
}