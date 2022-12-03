<?php

declare(strict_types=1);

namespace Larmias\Console\Input;

use Larmias\Console\Contracts\InputInterface;

class Input implements InputInterface
{
    /** @var string */
    protected string $scriptFile;

    /** @var string */
    protected string $command = '';

    /** @var array */
    protected array $tokens;

    /** @var array */
    protected array $options = [];

    /**
     * @param array|null $argv
     */
    public function __construct(?array $argv = null)
    {
        if (\is_null($argv)) {
            $argv = $_SERVER['argv'];
        }
        $this->scriptFile = (string)array_shift($argv);
        if (isset($argv[0]) && substr($argv[0], 0, 1) !== '-' && substr($argv[0], 0, 2) !== '--') {
            $this->command = (string)array_shift($argv);
        }
        $this->tokens = (array)$argv;
    }

    public function bool(): bool
    {
        return false;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        if (empty($this->options)) {
            $this->parse();
        }
        return $this->options;
    }

    /**
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * @return void
     */
    protected function parse(): void
    {
        foreach ($this->tokens as $key => $item) {
            $name = $item;
            $value = '';
            if (str_contains($name, '=')) {
                [$name] = explode('=', $item);
                $value = ltrim(strstr($item, "="), "=");
            }
            if (substr($name, 0, 2) == '--' || substr($name, 0, 1) == '-') {
                if (substr($name, 0, 1) == '-' && $value === '' && isset($argv[$key + 1]) && substr($argv[$key + 1], 0, 1) != '-') {
                    $next = $argv[$key + 1];
                    if (preg_match('/^[\S\s]+$/i', $next)) {
                        $value = $next;
                    }
                }
            } else {
                $name = '';
            }
            if ($name !== '') {
                $this->options[$name] = $value;
            }
        }
    }

    /**
     * @return string
     */
    public function getScriptFile(): string
    {
        return $this->scriptFile;
    }
}