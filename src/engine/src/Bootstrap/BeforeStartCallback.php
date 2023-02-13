<?php

declare(strict_types=1);

namespace Larmias\Engine\Bootstrap;

use Larmias\Contracts\StdoutLoggerInterface;
use Larmias\Engine\Contracts\WorkerInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Engine\Events\BeforeStart;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class BeforeStartCallback
{
    /**
     * @var EventDispatcherInterface|null
     */
    protected ?EventDispatcherInterface $eventDispatcher = null;

    /**
     * @var StdoutLoggerInterface|null
     */
    protected ?StdoutLoggerInterface $logger = null;

    /**
     * EngineStartCallback constructor.
     *
     * @param ContainerInterface $container
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(protected ContainerInterface $container)
    {
        if ($this->container->has(EventDispatcherInterface::class)) {
            $this->eventDispatcher = $this->container->get(EventDispatcherInterface::class);
        }

        if ($this->container->has(StdoutLoggerInterface::class)) {
            $this->logger = $this->container->get(StdoutLoggerInterface::class);
        }
    }

    /**
     * @param WorkerInterface $worker
     * @return void
     */
    public function onBeforeStart(WorkerInterface $worker): void
    {
        $this->eventDispatcher && $this->eventDispatcher->dispatch(new BeforeStart($worker));
        if ($this->logger) {
            $this->logger->writeln('<info>[INFO]Larmias start...</info>');
            $this->logger->writeln('<info>[INFO]' . $this->welcome() . '</info>');
        }
    }

    /**
     * @return string
     */
    protected function welcome(): string
    {
        return '
 ___       ________  ________  _____ ______   ___  ________  ________      
|\  \     |\   __  \|\   __  \|\   _ \  _   \|\  \|\   __  \|\   ____\     
\ \  \    \ \  \|\  \ \  \|\  \ \  \\\__\ \  \ \  \ \  \|\  \ \  \___|_    
 \ \  \    \ \   __  \ \   _  _\ \  \\|__| \  \ \  \ \   __  \ \_____  \   
  \ \  \____\ \  \ \  \ \  \\  \\ \  \    \ \  \ \  \ \  \ \  \|____|\  \  
   \ \_______\ \__\ \__\ \__\\ _\\ \__\    \ \__\ \__\ \__\ \__\____\_\  \ 
    \|_______|\|__|\|__|\|__|\|__|\|__|     \|__|\|__|\|__|\|__|\_________\
                                                               \|_________|';
    }
}