<?php

declare(strict_types=1);

namespace Larmias\Contracts\Annotation;

interface AnnotationInterface
{
    /**
     * 添加注解处理器
     * @param string|array $annotations
     * @param string $handler
     * @return AnnotationInterface
     */
    public function addHandler(string|array $annotations, string $handler): AnnotationInterface;

    /**
     * 解析类注解
     * @param string|object $class
     * @return void
     */
    public function parse(string|object $class): void;

    /**
     * 执行类注解处理
     * @return void
     */
    public function handle(): void;
}