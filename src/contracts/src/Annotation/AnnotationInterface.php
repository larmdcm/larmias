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
     * 添加扫描路径
     * @param string|array $path
     * @return AnnotationInterface
     */
    public function addIncludePath(string|array $path): AnnotationInterface;

    /**
     * 添加扫描排除路径
     * @param string|array $path
     * @return AnnotationInterface
     */
    public function addExcludePath(string|array $path): AnnotationInterface;

    /**
     * 执行扫描
     * @return void
     */
    public function scan(): void;
}