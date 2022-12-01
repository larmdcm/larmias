<?php

declare(strict_types=1);

namespace Larmias\Console\Input;

use Larmias\Console\Contracts\InputInterface;

class Input implements InputInterface
{
    /** @var string */
    protected string $scriptFile;

    /** @var string */
    protected string $command;

    /** @var array */
    protected array $tokens;


    /**
     * @param array|null $argv
     */
    public function __construct(?array $argv = null)
    {
        if (\is_null($argv)) {
            $argv = $_SERVER['argv'];
        }
        $this->scriptFile = array_shift($argv);
        $this->command = array_shift($argv);
        $this->tokens = $argv;
    }

    public function bool(): bool
    {
        return false;
    }

    public function getOption()
    {

    }

    /**
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    protected function parse()
    {
    }

    /**
     * @return string
     */
    public function getScriptFile(): string
    {
        return $this->scriptFile;
    }
}