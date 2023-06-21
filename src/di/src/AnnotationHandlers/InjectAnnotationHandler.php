<?php

declare(strict_types=1);

namespace Larmias\Di\AnnotationHandlers;

use Larmias\Contracts\Annotation\AnnotationHandlerInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Di\Annotation\Inject;
use Larmias\Di\Annotation\Scope;
use Larmias\Di\AnnotationCollector;
use Larmias\Utils\Reflection\ReflectUtil;
use ReflectionObject;
use ReflectionProperty;
use Throwable;
use RuntimeException;
use function get_class;
use function current;

class InjectAnnotationHandler implements AnnotationHandlerInterface
{
    /**
     * @var array
     */
    protected static array $inject = [];

    /**
     * @param ContainerInterface $container
     */
    public function __construct(protected ContainerInterface $container)
    {
        $this->container->resolving(function ($object) {
            $class = get_class($object);
            if (!isset(static::$inject[$class])) {
                return;
            }
            $refObject = new ReflectionObject($object);
            foreach (static::$inject[$class] as $property => $item) {
                $value = null;
                $scope = $this->getScope($class, $property);
                $newInstance = $scope && $scope->type === Scope::PROTOTYPE;
                if ($item['scope']) {
                    $newInstance = $item['scope'] === Scope::PROTOTYPE;
                }
                try {
                    $value = $this->container->make($item['name'], [], $newInstance);
                } catch (Throwable $e) {
                    if ($item['required']) {
                        throw $e;
                    }
                }
                if (isset($value)) {
                    $reflectorProperty = $refObject->getProperty($property);
                    ReflectUtil::setProperty($reflectorProperty, $object, $value);
                }
            }
        });
    }

    /**
     * @return void
     */
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
        /** @var Inject $value */
        $value = $param['value'][0];
        $name = $value->name;
        if (!$name) {
            $reflectionProperty = new ReflectionProperty($param['class'], $param['property']);
            if ($reflectionProperty->hasType() && !$reflectionProperty->getType()->isBuiltin()) {
                $name = $reflectionProperty->getType()->getName();
            } else {
                throw new RuntimeException('Inject annotation must have type');
            }
        }

        static::$inject[$param['class']][$param['property']] = [
            'name' => $name,
            'required' => $value->required,
            'scope' => $value->scope,
            'params' => $value->toArray(),
        ];
    }

    /**
     * @param string $class
     * @param string $property
     * @return Scope|null
     */
    protected function getScope(string $class, string $property): ?Scope
    {
        $annotations = AnnotationCollector::get(sprintf('%s.%s.%s.%s', $class, 'property', $property, Scope::class));
        if (empty($annotations)) {
            return null;
        }
        return current($annotations);
    }
}