<?php

declare(strict_types=1);

namespace Larmias\Console;

use Larmias\Console\Contracts\InputInterface;
use Larmias\Console\Contracts\OutputHandlerInterface;
use Larmias\Console\Contracts\OutputInterface;
use Larmias\Console\Contracts\OutputTableInterface;
use Larmias\Console\Input\Input;
use Larmias\Console\Output\Output;
use Larmias\Contracts\ConsoleInterface;
use Larmias\Contracts\ContainerInterface;

class Console implements ConsoleInterface
{
    /** @var string */
    public const VERSION = '1.0.0';

    /** @var InputInterface */
    protected InputInterface $input;

    /** @var OutputInterface */
    protected OutputInterface $output;

    /** @var array */
    protected array $commands = [
        'version' => Commands\Version::class,
        'help' => Commands\Help::class,
    ];

    /** @var bool */
    protected bool $autoExit = true;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(protected ContainerInterface $container)
    {
        $this->container->bind([
            InputInterface::class => Input::class,
            OutputInterface::class => Output::class,
            OutputHandlerInterface::class => \Larmias\Console\Output\Handler\Console::class,
            OutputTableInterface::class => \Larmias\Console\Output\Table::class,
        ]);
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function initialize(): void
    {
        $this->input = $this->container->get(InputInterface::class);
        $this->output = $this->container->get(OutputInterface::class);
    }

    /**
     * @param string $commandClass
     * @param string $name
     * @return $this
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function addCommand(string $commandClass, string $name = ''): self
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
     * @param string $name
     * @return \Larmias\Console\Command
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getCommand(string $name): Command
    {
        if (!$this->hasCommand($name)) {
            throw new \InvalidArgumentException(sprintf('The command "%s" does not exist.', $name));
        }
        /** @var \Larmias\Console\Command $command */
        $command = $this->commands[$name];
        if (is_string($command)) {
            $command = $this->container->get($command);
            $command->setConsole($this);
        }
        return $command;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasCommand(string $name): bool
    {
        return isset($this->commands[$name]);
    }

    /**
     * @return int
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function run(): int
    {
        $this->initialize();
        $name = $this->input->getCommand();
        $command = $name ? $this->getCommand($name) : $this->getCommand('help');
        try {
            $exitCode = $this->doRunCommand($command);
        } catch (\Throwable $e) {
            $this->output->writeln($e->getFile() . '('. $e->getLine() .')' . ':' . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
            $exitCode = $e->getCode();
            if (is_numeric($exitCode)) {
                $exitCode = (int)$exitCode;
                if (0 === $exitCode) {
                    $exitCode = 1;
                }
            } else {
                $exitCode = 1;
            }
        }

        if ($this->autoExit) {
            if ($exitCode > 255) {
                $exitCode = 255;
            }
            exit($exitCode);
        }

        return $exitCode;
    }

    /**
     * @param \Larmias\Console\Command $command
     * @return int
     */
    protected function doRunCommand(Command $command): int
    {
        return $command->run($this->input, $this->output);
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return self::VERSION;
    }

    /**
     * @return array
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * @param bool $autoExit
     */
    public function setAutoExit(bool $autoExit): void
    {
        $this->autoExit = $autoExit;
    }
}