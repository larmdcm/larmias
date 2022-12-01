<?php

declare(strict_types=1);

namespace Larmias\Console;

use Larmias\Console\Contracts\InputInterface;
use Larmias\Console\Contracts\OutputInterface;
use Larmias\Console\Input\Input;
use Larmias\Console\Output\Output;
use Larmias\Contracts\ContainerInterface;

class Console
{
    /** @var InputInterface */
    protected InputInterface $input;

    /** @var OutputInterface */
    protected OutputInterface $output;

    /**
     * @var array
     */
    protected array $commands = [];

    /**
     * @param ContainerInterface $container
     */
    public function __construct(protected ContainerInterface $container)
    {
        $this->container->bind([
            InputInterface::class => Input::class,
            OutputInterface::class => Output::class,
        ]);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    protected function initialize(): void
    {
        $this->input = $this->container->get(InputInterface::class);
        $this->output = $this->container->get(OutputInterface::class);
    }

    /**
     * @param string $commandClass
     * @param string $name
     * @return self
     * @throws \ReflectionException
     */
    public function addCommand(string $commandClass,string $name = ''): self
    {
        if ($name) {
            $this->commands[$name] = $commandClass;
        } else {
            /** @var Command $command */
            $command = $this->container->get($commandClass);
            $command->setConsole($this);
            $this->commands[$command->getName()] = $command;
        }
        return $this;
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function run(): void
    {
        $this->initialize();
        try {

        } catch (\Throwable $e) {

        }
    }
}