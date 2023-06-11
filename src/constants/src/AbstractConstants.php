<?php

declare(strict_types=1);

namespace Larmias\Constants;

use Larmias\Contracts\TranslatorInterface;
use Larmias\Constants\Annotation\Dict;
use Larmias\Constants\Annotation\Text;
use ReflectionClass;
use function array_merge;
use function str_starts_with;

/**
 * @method static mixed getText(mixed $value, ...$args)
 */
abstract class AbstractConstants
{
    /**
     * @var array
     */
    protected static array $data = [];

    /**
     * @var TranslatorInterface|null
     */
    protected static ?TranslatorInterface $translator = null;

    /**
     * @param string $name
     * @param array $args
     * @return mixed
     * @throws \ReflectionException
     */
    public static function __callStatic(string $name, array $args): mixed
    {
        if (!str_starts_with($name, 'get')) {
            throw new ConstantsException('The function is not defined.');
        }

        if (empty($args)) {
            throw new ConstantsException('The Arguments is required.');
        }

        $name = strtolower(substr($name, 3));
        $value = $args[0];

        $result = static::all()[$value][$name] ?? null;

        if (static::$translator && is_string($result)) {
            return static::$translator->trans($result, $args[1] ?? [], $args[2] ?? null);
        }

        return $result;
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function all(): array
    {
        $class = static::class;
        if (!isset(static::$data[$class])) {
            $data = [];
            $reflection = new ReflectionClass($class);
            foreach ($reflection->getReflectionConstants() as $reflectionConstant) {
                $attributes = $reflectionConstant->getAttributes();
                foreach ($attributes as $item) {
                    $attribute = $item->newInstance();
                    $value = $reflectionConstant->getValue();
                    if (!isset($data[$value])) {
                        $data[$value] = [];
                    }
                    if ($attribute instanceof Dict) {
                        $data[$value] = array_merge($data[$value], $attribute->value);
                    } else if ($attribute instanceof Text) {
                        $data[$value]['text'] = $attribute->text;
                    }
                }
            }
            static::$data[$class] = $data;
        }
        return static::$data[$class];
    }

    /**
     * @return TranslatorInterface|null
     */
    public static function getTranslator(): ?TranslatorInterface
    {
        return self::$translator;
    }

    /**
     * @param TranslatorInterface|null $translator
     */
    public static function setTranslator(?TranslatorInterface $translator): void
    {
        self::$translator = $translator;
    }
}