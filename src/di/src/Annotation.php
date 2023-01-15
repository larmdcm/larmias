<?php

declare(strict_types=1);

namespace Larmias\Di;

use Larmias\Contracts\ContainerInterface;
use Larmias\Di\Contracts\AnnotationHandlerInterface;
use Larmias\Di\Contracts\AnnotationInterface;
use Larmias\Utils\FileSystem\Finder;
use Larmias\Utils\Reflection\ReflectionManager;
use Larmias\Utils\Reflection\ReflectUtil;
use ReflectionClass;
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
            $realPath = $file->getRealPath();
            $classes = ReflectUtil::getAllClassesInFile($realPath);
            if (isset($classes[1])) {
                require_once $realPath;
            }

            foreach ($classes as $class) {
                $this->parse($class);
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
    protected function parse(string|object $class): void
    {
        if (\is_string($class) && !\class_exists($class)) {
            return;
        }
        $refClass = ReflectionManager::reflectClass($class);
        // 解析类注解
        $this->parseClassAnnotation($refClass);
        // 解析方法注解
        foreach ($refClass->getMethods() as $refMethod) {
            $this->parseMethodAnnotation($refClass, $refMethod);
            // 解析方法参数注解
            foreach ($refMethod->getParameters() as $refParameter) {
                $this->parseMethodParameterAnnotation($refClass, $refMethod, $refParameter);
            }
        }
        // 解析属性注解
        foreach ($refClass->getProperties() as $refProperty) {
            $this->parsePropertiesAnnotation($refClass, $refProperty);
        }
    }

    protected function parseClassAnnotation(ReflectionClass $refClass): void
    {
        $annotations = $this->getClassAnnotation($refClass);
        AnnotationCollector::collectClass($refClass->getName(), $annotations);
    }

    protected function getClassAnnotation(ReflectionClass $refClass): array
    {
        $annotations = $this->buildAttribute($refClass->getAttributes());
        $parentClass = $refClass->getParentClass();
        if ($parentClass) {
            $parentAnnotations = $this->getClassAnnotation($parentClass);
            return $this->handleAnnotationExtend($annotations, $parentAnnotations);
        }
        return $annotations;
    }

    protected function parseMethodAnnotation(ReflectionClass $refClass, ReflectionMethod $refMethod): void
    {
        AnnotationCollector::collectMethod($refClass->getName(), $refMethod->getName(), $this->buildAttribute($refMethod->getAttributes()));
    }

    protected function parseMethodParameterAnnotation(ReflectionClass $refClass, ReflectionMethod $refMethod, ReflectionParameter $refParameter): void
    {
        AnnotationCollector::collectMethodParam($refClass->getName(), $refMethod->getName(), $refParameter->getName(), $this->buildAttribute($refParameter->getAttributes()));
    }

    /**
     * @throws \ReflectionException
     */
    protected function parsePropertiesAnnotation(ReflectionClass $refClass, ReflectionProperty $refProperty): void
    {
        $annotations = $this->getPropertiesAnnotation($refProperty);
        AnnotationCollector::collectProperty($refClass->getName(), $refProperty->getName(), $annotations);
    }

    /**
     * @throws \ReflectionException
     */
    protected function getPropertiesAnnotation(ReflectionProperty $refProperty): array
    {
        $annotations = $this->buildAttribute($refProperty->getAttributes());
        $parentClass = $refProperty->getDeclaringClass()->getParentClass();
        if ($parentClass && $parentClass->hasProperty($refProperty->getName())) {
            $parentProperty = $parentClass->getProperty($refProperty->getName());
            $parentAnnotations = $this->getPropertiesAnnotation($parentProperty);
            return $this->handleAnnotationExtend($annotations, $parentAnnotations);
        }
        return $annotations;
    }

    protected function handleAnnotationExtend(array $annotations, array $parentAnnotations): array
    {
        return \array_merge($parentAnnotations, $annotations);
    }

    protected function buildAttribute(array $attributes): array
    {
        $annotations = [];
        foreach ($attributes as $attribute) {
            if ($attribute instanceof ReflectionAttribute) {
                if (\class_exists($attribute->getName())) {
                    $annotations[$attribute->getName()][] = $attribute->newInstance();
                }
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