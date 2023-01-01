<?php

declare(strict_types=1);

namespace Larmias\Di;

use Larmias\Contracts\ContainerInterface;
use Larmias\Di\Contracts\AnnotationHandlerInterface;
use Larmias\Di\Contracts\AnnotationInterface;
use Larmias\Utils\FileSystem\Finder;
use Larmias\Utils\Reflection\ReflectionManager;
use Larmias\Utils\Reflection\ReflectUtil;
use ReflectionAttribute;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

class Annotation implements AnnotationInterface
{
    protected array $config = [
        'include_path' => [],
        'exclude_path' => [],
        'handlers' => [],
    ];

    protected array $handler = [];

    public function __construct(protected ContainerInterface $container, array $config = [])
    {
        $this->config = \array_merge($this->config, $config);
    }

    /**
     * @throws \ReflectionException
     */
    public function scan(): void
    {
        $files = Finder::create()->include($this->config['include_path'])->exclude($this->config['exclude_path'])->includeExt('php')->files();
        foreach ($files as $file) {
            $pathname = $file->getPathname();
            $classes = ReflectUtil::getAllClassesInFile($pathname);
            if (isset($classes[1])) {
                require_once $file->getRealPath();
            }

            foreach ($classes as $class) {
                $this->parseClassAnnotation($class);
            }
        }
        $this->handle();
    }

    protected function handle(): void
    {
        foreach ((array)$this->config['handlers'] as $annotation => $handler) {
            $this->addHandler($annotation, $handler);
        }

        $items = AnnotationCollector::all();
        $handlers = [];
        foreach ($items as $item) {
            $handlerClass = $this->handler[$item['annotation']] ?? null;
            if ($handlerClass) {
                /** @var AnnotationHandlerInterface $handler */
                $handler = $this->container->make($handlerClass);
                $handler->collect($item);
                $handlers[$handlerClass] = $handler;
            }
        }

        foreach ($handlers as $handler) {
            $handler->handle();
        }
    }

    /**
     * @throws \ReflectionException
     */
    protected function parseClassAnnotation(string|object $class): void
    {
        $refClass = ReflectionManager::reflectClass($class);
        // 收集类注解
        AnnotationCollector::collectClass($refClass->getName(), $this->buildAttribute($refClass->getAttributes()));
        // 获取方法注解
        foreach ($refClass->getMethods() as $refMethod) {
            $this->parseMethodAnnotation($refMethod);
            // 获取方法参数注解
            foreach ($refMethod->getParameters() as $refParameter) {
                $this->parseMethodParameterAnnotation($refMethod, $refParameter);
            }
        }
        // 获取属性注解
        foreach ($refClass->getProperties() as $refProperty) {
            $this->parsePropertiesAnnotation($refProperty);
        }
    }

    protected function parseMethodAnnotation(ReflectionMethod $refMethod): void
    {
        AnnotationCollector::collectMethod($refMethod->class, $refMethod->getName(), $this->buildAttribute($refMethod->getAttributes()));
    }

    protected function parseMethodParameterAnnotation(ReflectionMethod $refMethod, ReflectionParameter $refParameter): void
    {
        AnnotationCollector::collectMethodParam($refMethod->class, $refMethod->getName(), $refParameter->getName(), $this->buildAttribute($refParameter->getAttributes()));
    }

    protected function parsePropertiesAnnotation(ReflectionProperty $refProperty): void
    {
        AnnotationCollector::collectProperty($refProperty->class, $refProperty->getName(), $this->buildAttribute($refProperty->getAttributes()));
    }

    protected function buildAttribute(array $attributes): array
    {
        $annotations = [];
        foreach ($attributes as $attribute) {
            if ($attribute instanceof ReflectionAttribute) {
                $annotations[$attribute->getName()] = $attribute->newInstance();
            }
        }
        return $annotations;
    }

    public function addHandler(string|array $annotations, string $handler): self
    {
        foreach ((array)$annotations as $item) {
            $this->handler[$item] = $handler;
        }
        return $this;
    }
}