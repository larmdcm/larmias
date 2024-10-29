<?php

declare(strict_types=1);

namespace Larmias\Contracts\Di;

interface ClassScannerInterface
{
    /**
     * 添加扫描路径
     * @param string|array $path
     * @return ClassScannerInterface
     */
    public function addIncludePath(string|array $path): ClassScannerInterface;

    /**
     * 添加扫描排除路径
     * @param string|array $path
     * @return ClassScannerInterface
     */
    public function addExcludePath(string|array $path): ClassScannerInterface;

    /**
     * 添加类路径
     * @param string $class
     * @param string $realpath
     * @return ClassScannerInterface
     */
    public function addClass(string $class, string $realpath): ClassScannerInterface;

    /**
     * 生成代理类
     * @return void
     */
    public function scanGenerateProxyClassMap(): void;

    /**
     * 执行扫描
     * @return void
     */
    public function scan(): void;
}