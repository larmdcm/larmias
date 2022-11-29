<?php

declare(strict_types=1);

namespace Larmias\Console\Input;

use Larmias\Console\Contracts\InputInterface;

class Input implements InputInterface
{
    /** @var string */
    protected string $scriptFile;

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
        $this->scriptFile = $argv[0];
        array_shift($argv);
        $this->tokens = $argv;
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