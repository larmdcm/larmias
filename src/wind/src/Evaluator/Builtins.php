<?php

declare(strict_types=1);

namespace Larmias\Wind\Evaluator;

use Larmias\Wind\Object\ArrayObject;
use Larmias\Wind\Object\Builtin;
use Larmias\Wind\Object\BuiltinFunction;
use Larmias\Wind\Object\Integer;
use Larmias\Wind\Object\ObjectInterface;
use Larmias\Wind\Object\StringObject;

class Builtins
{
    /**
     * @var Builtin[]
     */
    protected static array $builtins = [];

    /**
     * @param string $name
     * @param Builtin $builtin
     * @return void
     */
    public static function register(string $name, Builtin $builtin): void
    {
        static::$builtins[$name] = $builtin;
    }

    public static function get(string $name): ?Builtin
    {
        return static::$builtins[$name] ?? null;
    }

    public static function len(ObjectInterface $object): ?ObjectInterface
    {
        if ($object instanceof StringObject) {
            return new Integer(strlen($object->value));
        }

        if ($object instanceof ArrayObject) {
            return new Integer(count($object->elements));
        }

        return Evaluator::newError('arguments to `len` not supported,got %s', $object->getType()->getValue());
    }

    public static function first(ObjectInterface $object): ?ObjectInterface
    {
        if ($object instanceof ArrayObject) {
            return $object->elements[0] ?? Evaluator::$cache['nil'];
        }

        return Evaluator::newError('arguments to `first` must be ARRAY,got %s', $object->getType()->getValue());
    }


    public static function last(ObjectInterface $object): ?ObjectInterface
    {
        if ($object instanceof ArrayObject) {
            return $object->elements[count($object->elements) - 1] ?? Evaluator::$cache['nil'];
        }

        return Evaluator::newError('arguments to `last` must be ARRAY,got %s', $object->getType()->getValue());
    }

    public static function rest(ObjectInterface $object): ?ObjectInterface
    {
        if ($object instanceof ArrayObject) {

            if (count($object->elements) <= 0) {
                return Evaluator::$cache['nil'];
            }

            $elements = $object->elements;

            array_splice($elements, 0, 1);
            return new ArrayObject($elements);
        }

        return Evaluator::newError('arguments to `rest` must be ARRAY,got %s', $object->getType()->getValue());
    }

    public static function push(ObjectInterface $object, ObjectInterface $value): ?ObjectInterface
    {
        if ($object instanceof ArrayObject) {
            $elements = $object->elements;
            $elements[] = $value;
            return new ArrayObject($elements);
        }

        return Evaluator::newError('arguments to `push` must be ARRAY,got %s', $object->getType()->getValue());
    }

    public static function puts(...$args): ?ObjectInterface
    {
        /** @var ObjectInterface $arg */
        foreach ($args as $arg) {
            echo $arg->inspect() . PHP_EOL;
        }

        return Evaluator::$cache['nil'];
    }
}

Builtins::register('len', new Builtin(new BuiltinFunction([Builtins::class, 'len'])));
Builtins::register('first', new Builtin(new BuiltinFunction([Builtins::class, 'first'])));
Builtins::register('last', new Builtin(new BuiltinFunction([Builtins::class, 'last'])));
Builtins::register('rest', new Builtin(new BuiltinFunction([Builtins::class, 'rest'])));
Builtins::register('push', new Builtin(new BuiltinFunction([Builtins::class, 'push'])));
Builtins::register('puts', new Builtin(new BuiltinFunction([Builtins::class, 'puts'])));