<?php

declare(strict_types=1);

namespace Larmias\Contracts;

use Closure;

interface PipelineInterface
{
    /**
     * 初始数据
     *
     * @param mixed $passable
     * @return PipelineInterface
     */
    public function send(mixed $passable): PipelineInterface;

    /**
     * 调用栈
     *
     * @param array $pipes
     * @return self
     */
    public function through(array $pipes): PipelineInterface;

    /**
     * 执行
     *
     * @param Closure $destination
     * @return mixed
     */
    public function then(Closure $destination): mixed;

    /**
     * 设置异常处理器
     *
     * @param callable $handler
     * @return PipelineInterface
     */
    public function whenException(callable $handler): PipelineInterface;

}