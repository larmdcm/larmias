<?php

declare(strict_types=1);

namespace Larmias\Console;

use Larmias\Console\Contracts\InputInterface;
use Larmias\Console\Contracts\OutputInterface;
use Larmias\Console\Input\Option;

abstract class Command
{
    /** @var string */
    protected string $name;

    /** @var string */
    protected string $description = '';

    /** @var array */
    protected array $options = [];

    /** @var InputInterface */
    protected InputInterface $input;

    /** @var OutputInterface */
    protected OutputInterface $output;

    /** @var Console */
    protected Console $console;

    /**
     * Command __construct
     */
    public function __construct()
    {
        $this->configure();
        if (!$this->name) {
            throw new \LogicException(sprintf('The command defined in "%s" cannot have an empty name.', get_class($this)));
        }
    }

    /**
     * @return void
     */
    abstract public function handle(): void;

    /**
     * @return void
     */
    public function configure(): void
    {
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;
        $this->handle();
        return 0;
    }


    /**
     * @param string $name
     * @param string $shortcut
     * @param int $mode
     * @param string $description
     * @param mixed|null $default
     * @return self
     */
    public function addOption(string $name, string $shortcut, int $mode = Option::VALUE_NONE, string $description = '', mixed $default = null): self
    {
        $this->options[] = new Option($name, $shortcut, $mode, $description, $default);
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return self
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return Console
     */
    public function getConsole(): Console
    {
        return $this->console;
    }

    /**
     * @param Console $console
     */
    public function setConsole(Console $console): void
    {
        $this->console = $console;
    }
}