<?php

declare(strict_types=1);

namespace Larmias\Enum;

use Closure;
use Larmias\Contracts\TranslatorInterface;
use Larmias\Enum\Annotation\Text;
use UnexpectedValueException;
use BadMethodCallException;
use ReflectionClass;
use function get_class;
use function in_array;
use function array_key_exists;
use function array_search;
use function array_keys;
use function is_array;

abstract class AbstractEnum
{
    /**
     * @var Closure[]
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
     * @var array
     */
    protected static array $data = [];

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
            && static::class === get_class($variable);
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

    /**
     * @param mixed $value
     * @return string
     */
    public static function getText(mixed $value): string
    {
        $enum = new static($value);
        return $enum->getDataItem('text') ?: '';
    }

    /**
     * @param mixed $value
     * @return boolean
     */
    public static function isValid(mixed $value): bool
    {
        return in_array($value, static::toArray(), true);
    }

    /**
     * @param string $key
     * @return boolean
     */
    public static function isValidKey(string $key): bool
    {
        $array = static::toArray();
        return isset($array[$key]) || array_key_exists($key, $array);
    }

    /**
     * @param mixed $value
     * @return string
     */
    public static function assertValidValueReturningKey(mixed $value): string
    {
        if (false === ($key = static::search($value))) {
            throw new UnexpectedValueException("Value '$value' is not part of the enum " . static::class);
        }

        return $key;
    }

    /**
     * Return key for value
     *
     * @param mixed $value
     * @return bool|int|string
     */
    public static function search(mixed $value): bool|int|string
    {
        return array_search($value, static::toArray(), true);
    }

    /**
     * Returns the names (keys) of all constants in the Enum class
     *
     * @psalm-pure
     * @psalm-return list<string>
     * @return array
     */
    public static function keys(): array
    {
        return array_keys(static::toArray());
    }

    /**
     * Returns instances of the Enum class of all Enum constants
     *
     * @psalm-pure
     * @psalm-return array<string, static>
     * @return static[] Constant name in key, Enum instance in value
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
     * @param string|null $name
     * @return mixed
     */
    protected function getDataItem(?string $name = null): mixed
    {
        $value = $this->getValue();
        $data = static::mergeArray(static::getAnnotationData()[$value] ?? [], static::data()[$value] ?? []);
        if (is_array($data) && $name) {
            $result = $data[$name] ?? null;
            if ($name === 'text' && $result && isset($this->translator)) {
                return $this->translator->trans($result);
            }
            return $result;
        }
        return $data;
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
            $reflection = new ReflectionClass($class);
            static::$cache[$class] = $reflection->getConstants();
        }

        return static::$cache[$class];
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    protected static function getAnnotationData(): array
    {
        $class = static::class;
        if (!isset(static::$data[$class])) {
            $data = [];
            $reflection = new ReflectionClass($class);
            foreach ($reflection->getReflectionConstants() as $reflectionConstant) {
                $attributes = $reflectionConstant->getAttributes();
                foreach ($attributes as $item) {
                    $attribute = $item->newInstance();
                    if ($attribute instanceof Text) {
                        $data[$reflectionConstant->getValue()]['text'] = $attribute->text;
                    }
                }
            }
            static::$data[$class] = $data;
        }
        return static::$data[$class];
    }

    /**
     * AbstractEnum __callStatic.
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $class = static::class;
        if (!isset(self::$instances[$class][$name])) {
            $array = static::toArray();
            if (!isset($array[$name]) && !array_key_exists($name, $array)) {
                throw new BadMethodCallException("No static method or enum constant '$name' in class " . static::class);
            }
            self::$instances[$class][$name] = new static($array[$name]);
        }

        /** @var static $enum */
        $enum = self::$instances[$class][$name];
        if (empty($arguments)) {
            return $enum;
        }
        if ($arguments[0] === 'value') {
            return $enum->getValue();
        }
        return $enum->getDataItem($arguments[0]);
    }

    /**
     * @param array $array
     * @param array $distArray
     * @return array
     */
    private static function mergeArray(array $array, array $distArray): array
    {
        foreach ($distArray as $key => $item) {
            if (is_array($item) && isset($array[$key]) && is_array($array[$key])) {
                $array[$key] = static::mergeArray($array[$key], $item);
            } else {
                $array[$key] = $item;
            }
        }

        return $array;
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
