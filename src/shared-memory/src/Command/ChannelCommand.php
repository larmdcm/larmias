<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Command;

use Larmias\Contracts\Tcp\ConnectionInterface;
use Larmias\SharedMemory\ConnectionManager;
use Larmias\SharedMemory\Contracts\ChannelInterface;
use Larmias\SharedMemory\Message\Result;
use Larmias\SharedMemory\StoreManager;
use function count;

class ChannelCommand extends Command
{
    /**
     * @var ChannelInterface
     */
    protected ChannelInterface $channel;

    /**
     * @return void
     */
    protected function initialize(): void
    {
        $this->channel = StoreManager::channel();
    }

    /**
     * @return array
     */
    public function subscribe(): array
    {
        return [
            'type' => __FUNCTION__,
            'data' => $this->channel->subscribe($this->command->args, $this->getConnection()->getId())
        ];
    }

    /**
     * @return array
     */
    public function unsubscribe(): array
    {
        return [
            'type' => __FUNCTION__,
            'data' => $this->channel->unsubscribe($this->command->args, $this->getConnection()->getId())
        ];
    }

    /**
     * @return array
     */
    public function publish(): array
    {
        $result = $this->channel->publish($this->command->args[0]);
        foreach ($result as $item) {
            foreach ($item['id'] as $id) {
                $connection = ConnectionManager::get($id);
                $connection?->send(Result::build([
                    'type' => 'message',
                    'channel' => $item['channel'],
                    'data' => $this->command->args[1],
                ]));
            }
        }

        return [
            'type' => __FUNCTION__,
            'data' => count($result)
        ];
    }

    /**
     * @return array
     */
    public function channels(): array
    {
        return [
            'type' => __FUNCTION__,
            'data' => $this->channel->channels($this->getConnection()->getId())
        ];
    }

    /**
     * @return array
     */
    public function close(): array
    {
        return [
            'type' => __FUNCTION__,
            'data' => $this->channel->close($this->getConnection()->getId())
        ];
    }

    /**
     * @param ConnectionInterface $connection
     * @return void
     */
    public static function onClose(ConnectionInterface $connection): void
    {
        StoreManager::channel()->close($connection->getId());
    }
}