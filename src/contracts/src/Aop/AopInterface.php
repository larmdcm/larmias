<?php

declare(strict_types=1);

namespace Larmias\Contracts\Aop;

interface AopInterface
{
    /**
     * 生成代理类文件
     * @param string $class
     * @param string $realpath
     * @return string
     */
    public function generateProxyClass(string $class, string $realpath): string;
}