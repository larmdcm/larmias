<?php

declare(strict_types=1);

namespace Larmias\Config\Annotation\Handler;

use Larmias\Config\Annotation\Value;
use Larmias\Contracts\Annotation\AnnotationHandlerInterface;
use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Support\Reflection\ReflectUtil;
use ReflectionObject;

class ValueAnnotationHandler implements AnnotationHandlerInterface
{
    /**
     * @var array
     */
    protected static array $data = [];

    /**
     * @param ContainerInterface $container
     * @param ConfigInterface $config
     */
    public function __construct(protected ContainerInterface $container, protected ConfigInterface $config)
    {
        $this->container->resolving(function ($object) {
            $class = get_class($object);
            if (!isset(static::$data[$class])) {
                return;
            }
            $refObject = new ReflectionObject($object);
            foreach (static::$data[$class] as $property => $item) {
                $reflectorProperty = $refObject->getProperty($property);
                ReflectUtil::setProperty($reflectorProperty, $object, $this->config->get($item['key'], $item['default']));
            }
        });
    }

    /**
     * @param array $param
     * @return void
     */
    public function collect(array $param): void
    {
        if ($param['type'] !== 'property') {
            return;
        }
        /** @var Value $value */
        $value = $param['value'][0];
        static::$data[$param['class']][$param['property']] = [
            'key' => $value->key,
            'default' => $value->default,
        ];
    }

    /**
     * @return void
     */
    public function handle(): void
    {
    }
}