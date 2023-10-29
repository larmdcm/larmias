<?php

declare(strict_types=1);

namespace Larmias\Engine\Bootstrap;

use Larmias\Contracts\StdoutLoggerInterface;
use Larmias\Engine\Contracts\KernelInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Engine\Events\BeforeStart;
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
     * @throws \Throwable
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
     * @param KernelInterface $kernel
     * @return void
     */
    public function onBeforeStart(KernelInterface $kernel): void
    {
        $this->eventDispatcher && $this->eventDispatcher->dispatch(new BeforeStart($kernel));
        $settings = $kernel->getConfig()->getSettings();
        $logger = $settings['logger'] ?? true;
        if ($this->logger && $logger) {
            $this->logger->info('Larmias start...');
            $this->logger->info($this->getLogo());
        }
    }

    /**
     * @return string
     */
    protected function getLogo(): string
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