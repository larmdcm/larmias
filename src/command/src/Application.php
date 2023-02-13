<?php

declare(strict_types=1);

namespace Larmias\Command;

use Larmias\Contracts\ConsoleInterface;
use Larmias\Contracts\ContainerInterface;
use Symfony\Component\Console\Application as SymfonyApplication;
use Psr\EventDispatcher\EventDispatcherInterface;

class Application implements ConsoleInterface
{
    /**
     * @var SymfonyApplication
     */
    protected SymfonyApplication $application;

    /**
     * @var string
     */
    protected string $name = 'larmias';

    /**
     * @var string
     */
    protected string $version = '1.0.0';

    /**
     * @param ContainerInterface $container
     * @param EventDispatcherInterface|null $eventDispatcher
     */
    public function __construct(protected ContainerInterface $container, protected ?EventDispatcherInterface $eventDispatcher = null)
    {
        $this->application = new SymfonyApplication($this->name, $this->version);
    }

    /**
     * @param string $commandClass
     * @param string $name
     * @return ConsoleInterface
     */
    public function addCommand(string $commandClass, string $name = ''): ConsoleInterface
    {
        /** @var Command $command */
        $command = $this->container->make($commandClass, [], true);
        if ($name) {
            $command->setName($name);
        }
        if ($command instanceof Command) {
            $command->setContainer($this->container)->setEventDispatcher($this->eventDispatcher);
        }
        $this->application->add($command);
        return $this;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function run(): int
    {
        return $this->application->run();
    }
}