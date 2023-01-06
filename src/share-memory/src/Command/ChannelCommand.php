<?php

declare(strict_types=1);

namespace Larmias\ShareMemory\Command;

use Larmias\Contracts\Tcp\ConnectionInterface;
use Larmias\ShareMemory\ConnectionManager;
use Larmias\ShareMemory\Contracts\ChannelInterface;
use Larmias\ShareMemory\Message\Result;
use Larmias\ShareMemory\StoreManager;

class ChannelCommand extends Command
{
    protected ChannelInterface $channel;

    protected function initialize()
    {
        $this->channel = StoreManager::channel();
    }

    public function subscribe(): array
    {
        return [
            'type' => __FUNCTION__,
            'data' => $this->channel->subscribe($this->command->args, $this->getConnection()->getId())
        ];
    }

    public function unsubscribe()
    {
        return [
            'type' => __FUNCTION__,
            'data' => $this->channel->unsubscribe($this->command->args, $this->getConnection()->getId())
        ];
    }

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
            'data' => \count($result)
        ];
    }

    public function channels()
    {
        return [
            'type' => __FUNCTION__,
            'data' => $this->channel->channels($this->getConnection()->getId())
        ];
    }

    public function close()
    {
        return [
            'type' => __FUNCTION__,
            'data' => $this->channel->close($this->getConnection()->getId())
        ];
    }

    public static function onClose(ConnectionInterface $connection)
    {
        StoreManager::channel()->close($connection->getId());
    }
}