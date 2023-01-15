<?php

declare(strict_types=1);

namespace Larmias\Env;

use Larmias\Contracts\DotEnvInterface;
use InvalidArgumentException;
use ArrayAccess;

class DotEnv implements DotEnvInterface, ArrayAccess
{
    protected array $convert = [
        'true' => true,
        'false' => false,
        'on' => true,
        'off' => false,
        'null' => null,
    ];

    public function load(string $path): bool
    {
        if (!\is_file($path)) {
            return false;
        }

        if (!\is_readable($path)) {
            throw new InvalidArgumentException("The .env file is not readable: {$path}");
        }

        $lines = \file($path, \FILE_IGNORE_NEW_LINES | \FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = \trim($line);
            if (\str_starts_with($line, '#')) {
                continue;
            }

            if (\str_contains($line, '=')) {
                $lineSplit = \explode('=', $line, 2);
                [$name, $value] = $this->normaliseVariable($lineSplit[0], $lineSplit[1] ?? '');
                $this->setVariable($name, $value);
            }
        }

        return true;
    }

    public function get(string $name, mixed $default = null): mixed
    {
        $value = $this->getVariable($name);
        if ($value === null) {
            return $default;
        }
        return $this->convert[$value] ?? $value;
    }

    public function set(string|array $name, ?string $value = null): void
    {
        if (\is_array($name)) {
            foreach ($name as $key => $val) {
                $this->set($key, $val);
            }
        } else {
            [$name, $value] = $this->normaliseVariable($name, $value);
            $this->setVariable($name, $value);
        }
    }

    public function has(string $name): bool
    {
        return $this->getVariable($name) !== null;
    }

    protected function getVariable(string $name): ?string
    {
        if (\array_key_exists($name, $_ENV)) {
            return $_ENV[$name];
        }
        if (\array_key_exists($name, $_SERVER)) {
            return $_SERVER[$name];
        }
        $value = \getenv($name);
        return $value === false ? null : (string)$value;
    }

    protected function setVariable(string $name, string $value = ''): void
    {
        if (!\getenv($name, true)) {
            \putenv("{$name}={$value}");
        }

        if (empty($_ENV[$name])) {
            $_ENV[$name] = $value;
        }

        if (empty($_SERVER[$name])) {
            $_SERVER[$name] = $value;
        }
    }

    protected function normaliseVariable(string $name, string $value = ''): array
    {
        if (\str_contains($name, '=')) {
            [$name, $value] = explode('=', $name, 2);
        }

        $name = \trim($name);
        $value = \trim($value);

        $name = \str_replace(['export', '\'', '"'], '', $name);

        $value = $this->sanitizeValue($value);
        $value = $this->resolveNestedVariables($value);

        return [$name, $value];
    }

    protected function sanitizeValue(string $value): string
    {
        if (!$value) {
            return $value;
        }

        if (\strpbrk($value[0], '"\'') !== false) {
            $quote = $value[0];

            $regexPattern = \sprintf(
                '/^
                %1$s          # match a quote at the start of the value
                (             # capturing sub-pattern used
                 (?:          # we do not need to capture this
                 [^%1$s\\\\] # any character other than a quote or backslash
                 |\\\\\\\\   # or two backslashes together
                 |\\\\%1$s   # or an escaped quote e.g \"
                 )*           # as many characters that match the previous rules
                )             # end of the capturing sub-pattern
                %1$s          # and the closing quote
                .*$           # and discard any string after the closing quote
                /mx',
                $quote
            );

            $value = \preg_replace($regexPattern, '$1', $value);
            $value = \str_replace("\\{$quote}", $quote, $value);
            $value = \str_replace('\\\\', '\\', $value);
        } else {
            $parts = \explode(' #', $value, 2);
            $value = \trim($parts[0]);

            if (\preg_match('/\s+/', $value) > 0) {
                throw new InvalidArgumentException('.env values containing spaces must be surrounded by quotes.');
            }
        }

        return $value;
    }

    protected function resolveNestedVariables(string $value): string
    {
        if (\str_contains($value, '$')) {
            $value = \preg_replace_callback(
                '/\${([a-zA-Z0-9_\.]+)}/',
                function ($matchedPatterns) {
                    $nestedVariable = $this->getVariable($matchedPatterns[1]);

                    if ($nestedVariable === null) {
                        return $matchedPatterns[0];
                    }

                    return $nestedVariable;
                },
                $value
            );
        }
        return $value;
    }

    public function __get(string $name): mixed
    {
        return $this->get($name);
    }

    public function __set(string $name, ?string $value): void
    {
        $this->set($name, $value);
    }

    public function __isset(string $name): bool
    {
        return $this->has($name);
    }

    #[\ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset): mixed
    {
        return $this->get($offset);
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset): void
    {
        throw new \RuntimeException('not support: unset');
    }
}