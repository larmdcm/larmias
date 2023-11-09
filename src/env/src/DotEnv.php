<?php

declare(strict_types=1);

namespace Larmias\Env;

use Larmias\Contracts\DotEnvInterface;
use InvalidArgumentException;
use ArrayAccess;
use function is_file;
use function file;
use function trim;
use function str_starts_with;
use function is_readable;
use function str_contains;
use function explode;
use function is_array;
use function array_key_exists;
use function getenv;
use function str_replace;
use function strpbrk;
use function sprintf;
use function preg_replace;
use function preg_replace_callback;
use function preg_match;
use const FILE_IGNORE_NEW_LINES;
use const FILE_SKIP_EMPTY_LINES;

class DotEnv implements DotEnvInterface, ArrayAccess
{
    /**
     * @var array
     */
    protected array $convert = [
        'true' => true,
        'false' => false,
        'on' => true,
        'off' => false,
        'null' => null,
    ];

    /**
     * @param string $path
     * @return bool
     */
    public function load(string $path): bool
    {
        if (!is_file($path)) {
            return false;
        }

        if (!is_readable($path)) {
            throw new InvalidArgumentException("The .env file is not readable: {$path}");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);
            if (str_starts_with($line, '#')) {
                continue;
            }

            if (str_contains($line, '=')) {
                $lineSplit = explode('=', $line, 2);
                [$name, $value] = $this->normaliseVariable($lineSplit[0], $lineSplit[1] ?? '');
                $this->setVariable($name, $value);
            }
        }

        return true;
    }

    /**
     * @param string $name
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $name, mixed $default = null): mixed
    {
        $value = $this->getVariable($name);
        if ($value === null) {
            return $default;
        }

        return array_key_exists($value, $this->convert) ? $this->convert[$value] : $value;
    }

    /**
     * @param string|array $name
     * @param string|null $value
     * @return void
     */
    public function set(string|array $name, ?string $value = null): void
    {
        if (is_array($name)) {
            foreach ($name as $key => $val) {
                $this->set($key, $val);
            }
        } else {
            [$name, $value] = $this->normaliseVariable($name, $value);
            $this->setVariable($name, $value);
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return $this->getVariable($name) !== null;
    }

    /**
     * @param string $name
     * @return string|null
     */
    protected function getVariable(string $name): ?string
    {
        $collect = [$_ENV];
        $value = null;

        foreach ($collect as $data) {
            if (array_key_exists($name, $data)) {
                $value = $data[$name];
                break;
            }
        }

        if ($value === null) {
            $value = getenv($name);
        }

        return $value === false ? null : (string)$value;
    }

    /**
     * @param string $name
     * @param string $value
     * @return void
     */
    protected function setVariable(string $name, string $value = ''): void
    {
        putenv("{$name}={$value}");
        $_ENV[$name] = $value;
    }

    /**
     * @param string $name
     * @param string $value
     * @return array
     */
    protected function normaliseVariable(string $name, string $value = ''): array
    {
        if (str_contains($name, '=')) {
            [$name, $value] = explode('=', $name, 2);
        }

        $name = trim($name);
        $value = trim($value);

        $name = str_replace(['export', '\'', '"'], '', $name);

        $value = $this->sanitizeValue($value);
        $value = $this->resolveNestedVariables($value);

        return [$name, $value];
    }

    /**
     * @param string $value
     * @return string
     */
    protected function sanitizeValue(string $value): string
    {
        if (!$value) {
            return $value;
        }

        if (strpbrk($value[0], '"\'') !== false) {
            $quote = $value[0];

            $regexPattern = sprintf(
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

            $value = preg_replace($regexPattern, '$1', $value);
            $value = str_replace("\\{$quote}", $quote, $value);
            $value = str_replace('\\\\', '\\', $value);
        } else {
            $parts = explode(' #', $value, 2);
            $value = trim($parts[0]);

            if (preg_match('/\s+/', $value) > 0) {
                throw new InvalidArgumentException('.env values containing spaces must be surrounded by quotes.');
            }
        }

        return $value;
    }

    /**
     * @param string $value
     * @return string
     */
    protected function resolveNestedVariables(string $value): string
    {
        if (str_contains($value, '$')) {
            $value = preg_replace_callback(
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

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return $this->get($name);
    }

    /**
     * @param string $name
     * @param string|null $value
     * @return void
     */
    public function __set(string $name, ?string $value): void
    {
        $this->set($name, $value);
    }

    /**
     * @param string $name
     * @return bool
     */
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
        $this->set($offset, null);
    }
}