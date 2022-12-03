<?php

declare(strict_types=1);

namespace Larmias\Console\Commands;

use Larmias\Console\Command;
use Larmias\Console\Input\Option;

class Help extends Command
{
    /** @var string */
    protected string $name = 'help';

    /** @var string */
    protected string $description = 'Displays help for a command';

    /** @var string */
    protected string $commandName = '';

    /**
     * @return void
     */
    public function configure(): void
    {
        $this->addOption('help', 'h', Option::VALUE_NONE, 'Display this help message');
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function handle(): void
    {
        $this->output->writeln('version ' . $this->console->getVersion());
        $this->output->newLine();
        $this->output->writeln('Usage:' . PHP_EOL . '  command [options]');
        $this->printCommandOptions();
        $this->printCommands();

        var_dump($this->input->getOptions());
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function printCommands(): void
    {
        $commands = array_keys($this->console->getCommands());
        if (empty($commands)) {
            return;
        }
        $this->output->newLine();
        $this->output->writeln('Commands:');

        foreach ($commands as $name) {
            $command = $this->console->getCommand($name);
            $this->output->writeln("  {$command->getName()}\t{$command->getDescription()}");
        }
    }

    /**
     * @return void
     */
    protected function printCommandOptions(): void
    {
        if (empty($this->options)) {
            return;
        }
        $this->output->newLine();
        $this->output->writeln('Options:');

        /** @var \Larmias\Console\Input\Option $option */
        foreach ($this->options as $option) {
            $names = [];
            $shortcut = $option->getShortcut();
            if ($shortcut) {
                $names[] = '-' . $shortcut;
            }
            $names[] = '--' . $option->getName();
            $name = implode(', ', $names);
            $this->output->writeln("  {$name}\t{$option->getDescription()}");
        }
    }

    /**
     * @param string $commandName
     */
    public function setCommandName(string $commandName): void
    {
        $this->commandName = $commandName;
    }
}