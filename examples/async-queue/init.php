<?php

use Larmias\AsyncQueue\Contracts\JobHandlerInterface;
use Larmias\AsyncQueue\Contracts\MessageInterface;
use Larmias\AsyncQueue\Contracts\QueueDriverInterface;

/** @var \Larmias\Contracts\ContainerInterface $container */
$container = require '../di/container.php';


/** @var \Larmias\Contracts\ConfigInterface $config */
$config = $container->get(\Larmias\Contracts\ConfigInterface::class);
$config->load('./redis.php');
$config->load('./async_queue.php');
$container->bind(\Larmias\Contracts\Redis\RedisFactoryInterface::class, \Larmias\Redis\RedisFactory::class);
$container->bind(\Larmias\AsyncQueue\Contracts\QueueInterface::class, \Larmias\AsyncQueue\Queue::class);

class ExampleJobHandler implements JobHandlerInterface
{
    public function handle(\Larmias\AsyncQueue\Contracts\JobInterface $job): void
    {
        echo $job->getMessage()->getMessageId() . '-' . $job->getData()['name'] . ' job handler...' . PHP_EOL;
        $job->ack();
    }
}

return $container;