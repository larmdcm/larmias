<?php

declare(strict_types=1);

namespace Larmias\Di\AnnotationHandlers;

use Larmias\Contracts\ContainerInterface;
use Larmias\Di\Annotation\Inject;
use Larmias\Di\Contracts\AnnotationHandlerInterface;
use Larmias\Utils\Reflection\ReflectUtil;
use ReflectionProperty;
use ReflectionObject;

class InjectAnnotationHandler implements AnnotationHandlerInterface
{
    protected static array $inject = [];

    public function __construct(protected ContainerInterface $container)
    {
        $this->container->resolving(function ($object) {
            $class = \get_class($object);
            if (!isset(static::$inject[$class])) {
                return;
            }
            $refObject = new ReflectionObject($object);

            foreach (static::$inject[$class] as $property => $item) {
                $reflectorProperty = $refObject->getProperty($property);
                $value = $this->container->make($item['name']);
                ReflectUtil::setProperty($reflectorProperty,$object,$value);
            }
        });
    }

    public function handle(): void
    {
    }

    /**
     * @throws \ReflectionException
     */
    public function collect(array $param): void
    {
        if ($param['type'] !== 'property') {
            return;
        }
        $reflectionProperty = new ReflectionProperty($param['class'], $param['property']);
        /** @var Inject $value */
        $value = $param['value'];
        $name = $value->name;
        if (!$name) {
            if ($reflectionProperty->hasType() && !$reflectionProperty->getType()->isBuiltin()) {
                $name = $reflectionProperty->getType()->getName();
            } else {
                throw new \RuntimeException('Inject annotation must have type');
            }
        }

        static::$inject[$param['class']][$param['property']] = [
            'name' => $name,
            'params' => $value->toArray(),
        ];
    }
}