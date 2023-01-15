<?php

declare(strict_types=1);

namespace Larmias\Enum;

use Closure;
use Larmias\Contracts\TranslatorInterface;

abstract class AbstractEnum
{
    /**
     * @var array
     */
    protected static array $maker = [];

    /**
     * @var array
     */
    protected static array $instances = [];

    /**
     * @var array
     */
    protected static array $cache = [];

    /**
     * @var TranslatorInterface
     */
    protected TranslatorInterface $translator;

    /**
     * @var string
     */
    protected string $key;

    /**
     * @var mixed
     */
    protected mixed $value;

    /**
     * Enum __construct.
     *
     * @param mixed $value
     */
    public function __construct(mixed $value)
    {
        if ($value instanceof static) {
            $value = $value->getValue();
        }
        $this->key = static::assertValidValueReturningKey($value);
        $this->value = $value;

        foreach (static::$maker as $maker) {
            $maker($this);
        }
    }

    /**
     * @param Closure $maker
     * @return void
     */
    public static function maker(Closure $maker): void
    {
        static::$maker[] = $maker;
    }

    /**
     * @param TranslatorInterface $translator
     * @return self
     */
    public function setTranslator(TranslatorInterface $translator): self
    {
        $this->translator = $translator;
        return $this;
    }

    /**
     * Enum __get.
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->getDataItem($name);
    }

    /**
     * @param mixed $variable
     * @return bool
     */
    final public function equals(mixed $variable): bool
    {
        return $variable instanceof self
            && $this->getValue() === $variable->getValue()
            && static::class === \get_class($variable);
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    public static function getMessage(string $name): string
    {
        return '';
    }

    /**
     * @param mixed $value
     * @return boolean
     * @throws \ReflectionException
     */
    public static function isValid(mixed $value): bool
    {
        return in_array($value, static::toArray(), true);
    }

    /**
     * @param string $key
     * @return boolean
     * @throws \ReflectionException
     */
    public static function isValidKey(string $key): bool
    {
        $array = static::toArray();
        return isset($array[$key]) || \array_key_exists($key, $array);
    }

    /**
     * @param mixed $value
     * @return string
     */
    public static function assertValidValueReturningKey(mixed $value): string
    {
        if (false === ($key = static::search($value))) {
            throw new \UnexpectedValueException("Value '$value' is not part of the enum " . static::class);
        }

        return $key;
    }

    /**
     * Return key for value
     *
     * @param mixed $value
     * @return bool|int|string
     * @throws \ReflectionException
     */
    public static function search(mixed $value): bool|int|string
    {
        return \array_search($value, static::toArray(), true);
    }

    /**
     * Returns the names (keys) of all constants in the Enum class
     *
     * @psalm-pure
     * @psalm-return list<string>
     * @return array
     * @throws \ReflectionException
     */
    public static function keys(): array
    {
        return \array_keys(static::toArray());
    }

    /**
     * Returns instances of the Enum class of all Enum constants
     *
     * @psalm-pure
     * @psalm-return array<string, static>
     * @return static[] Constant name in key, Enum instance in value
     * @throws \ReflectionException
     */
    public static function values(): array
    {
        $values = [];

        foreach (static::toArray() as $key => $value) {
            $values[$key] = new static($value);
        }

        return $values;
    }

    /**
     * @return array
     */
    public static function data(): array
    {
        return [];
    }

    /**
     * @param string $name
     * @return mixed
     */
    protected function getDataItem(string $name): mixed
    {
        $data = static::data()[$this->getValue()] ?? [];
        return $data[$name] ?? null;
    }

    /**
     * Enum __toString.
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this->value;
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    protected static function toArray(): array
    {
        $class = static::class;

        if (!isset(static::$cache[$class])) {
            $reflection = new \ReflectionClass($class);
            static::$cache[$class] = $reflection->getConstants();
        }

        return static::$cache[$class];
    }

    /**
     * Enum __callStatic.
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \ReflectionException
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $class = static::class;
        if (!isset(self::$instances[$class][$name])) {
            $array = static::toArray();
            if (!isset($array[$name]) && !\array_key_exists($name, $array)) {
                throw new \BadMethodCallException("No static method or enum constant '$name' in class " . static::class);
            }
            self::$instances[$class][$name] = new static($array[$name]);
        }
        $enum = self::$instances[$class][$name];
        if (empty($arguments)) {
            return $enum;
        }
        return $enum->getDataItem($arguments[0]);
    }

    /**
     * Specify data which should be serialized to JSON. This method returns data that can be serialized by json_encode()
     * natively.
     *
     * @return mixed
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @psalm-pure
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize(): mixed
    {
        return $this->getValue();
    }
}
