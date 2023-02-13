<?php

declare(strict_types=1);

namespace Larmias\Console\Commands;

use Larmias\Console\Command;
use Larmias\Console\Input\Argument;
use Larmias\Console\Input\Option;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Help extends Command
{
    /** @var string */
    protected string $commandName = '';

    /** @var array */
    protected array $options = [];

    /**
     * @return void
     */
    public function configure(): void
    {
        $this->setName('help')
            ->setDescription('Displays help for a command')
            ->addArgument('commandName', Argument::OPTIONAL, ' The command name', '');
    }

    /**
     * @return int
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function handle(): int
    {
        $this->output->writeln(sprintf('version <comment>%s</comment>', $this->console->getVersion()));
        $this->output->newLine();
        $this->printUsage();
        $this->printCommandArguments();
        $this->printCommandOptions();
        $this->printCommands();
        $this->printCommandHelp();
        return self::SUCCESS;
    }

    /**
     * @return void
     */
    protected function printUsage(): void
    {
        $this->output->writeln(
            sprintf('<comment>Usage:</comment>' . PHP_EOL . '  php ' . $this->input->getScriptFile() . ' command %s%s',
                !empty($this->definition->getArguments()) ? '[arguments] ' : '', !empty($this->definition->getOptions()) ? '[options] ' : ''
            )
        );
    }

    /**
     * @return void
     */
    protected function printCommandArguments(): void
    {
        $arguments = $this->definition->getArguments();
        if (empty($arguments)) {
            return;
        }
        $this->output->newLine();
        $this->output->writeln('<comment>Arguments:</comment>');
        $printOption = $this->getOutputOptions();
        $totalWidth = $printOption['totalWidth'];
        foreach ($arguments as $argument) {
            $spacingWidth = $totalWidth - strlen($argument->getName()) + 2;
            $this->output->writeln(sprintf("  <info>{$argument->getName()}</info>%s%s{$argument->getDescription()}", str_repeat(' ', $spacingWidth), str_repeat(' ', $totalWidth)));
        }
    }

    /**
     * @return void
     */
    protected function printCommandOptions(): void
    {
        $options = $this->definition->getOptions();
        if (empty($options)) {
            return;
        }
        $this->output->newLine();
        $this->output->writeln('<comment>Options:</comment>');
        $printOption = $this->getOutputOptions('options');
        $totalWidth = $printOption['totalWidth'];

        foreach ($options as $option) {
            $name = $this->getOptionName($option);
            $spacingWidth = $totalWidth - strlen($name) + 2;
            $this->output->writeln(sprintf("  <info>{$name}</info>%s%s{$option->getDescription()}", str_repeat(' ', $spacingWidth), str_repeat(' ', $totalWidth)));
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function printCommands(): void
    {
        $commands = array_keys($this->console->getCommands());
        if (empty($commands)) {
            return;
        }
        $this->output->newLine();
        $this->output->writeln('<comment>Commands:</comment>');
        $printOption = $this->getOutputOptions('commands');
        $totalWidth = $printOption['totalWidth'];

        foreach ($commands as $name) {
            $command = $this->console->getCommand($name);
            $spacingWidth = $totalWidth - strlen($command->getName()) + 2;
            $this->output->writeln(sprintf("  <info>{$command->getName()}</info>%s%s{$command->getDescription()}", str_repeat(' ', $spacingWidth), str_repeat(' ', $totalWidth)));
        }
    }

    /**
     * @return void
     */
    protected function printCommandHelp(): void
    {
        $help = $this->getHelp();
        if (empty($help)) {
            return;
        }
        $this->output->newLine();
        $this->output->writeln('<comment>Help:</comment>');
        $this->output->writeln('  ' . $help);
    }

    /**
     * @param string $type
     * @return int[]
     */
    protected function getOutputOptions(string $type = 'arguments'): array
    {
        $totalWidth = 0;

        switch ($type) {
            case 'arguments':
                $totalWidth = $this->getMaxWidth($this->definition->getArguments());
                break;
            case 'options':
                $totalWidth = $this->getMaxWidth(array_map(fn($item) => $this->getOptionName($item), $this->definition->getOptions()));
                break;
            case 'commands':
                $totalWidth = $this->getMaxWidth(array_keys($this->console->getCommands()));
                break;
        }
        return [
            'totalWidth' => $totalWidth,
        ];
    }

    /**
     * @param Option $option
     * @return string
     */
    protected function getOptionName(Option $option): string
    {
        $names = [];
        $shortcut = $option->getShortcut();
        if ($shortcut) {
            $names[] = '-' . $shortcut;
        }
        $names[] = '--' . $option->getName();
        return implode(', ', $names);
    }

    /**
     * @param iterable $list
     * @return int
     */
    protected function getMaxWidth(iterable $list): int
    {
        $totalWidth = 0;
        foreach ($list as $item) {
            $totalWidth = max($totalWidth, strlen(is_object($item) ? $item->getName() : $item));
        }
        return $totalWidth;
    }

    /**
     * @param string $commandName
     */
    public function setCommandName(string $commandName): void
    {
        $this->commandName = $commandName;
    }
}