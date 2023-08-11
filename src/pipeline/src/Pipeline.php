<?php

declare(strict_types=1);

namespace Larmias\Pipeline;

use Larmias\Contracts\PipelineInterface;
use Throwable;
use Closure;
use function array_reduce;
use function array_reverse;
use function call_user_func;

class Pipeline implements PipelineInterface
{
    /**
     * @var mixed
     */
    protected mixed $passable;

    /**
     * @var array
     */
    protected array $pipes = [];

    /**
     * @var callable
     */
    protected $exceptionHandler = null;

    /**
     * 初始数据
     * @param mixed $passable
     * @return self
     */
    public function send(mixed $passable): self
    {
        $this->passable = $passable;
        return $this;
    }

    /**
     * 调用栈
     * @param array $pipes
     * @return self
     */
    public function through(array $pipes): self
    {
        $this->pipes = $pipes;
        return $this;
    }

    /**
     * 执行
     * @param Closure $destination
     * @return mixed
     * @throws Throwable
     */
    public function then(Closure $destination): mixed
    {
        $pipeline = array_reduce(
            array_reverse($this->pipes),
            $this->carry(),
            function ($passable) use ($destination) {
                try {
                    return $destination($passable);
                } catch (Throwable $e) {
                    return $this->handleException($passable, $e);
                }
            }
        );

        return $pipeline($this->passable);
    }

    /**
     * 设置异常处理器
     * @param callable $handler
     * @return self
     */
    public function whenException(callable $handler): self
    {
        $this->exceptionHandler = $handler;
        return $this;
    }

    /**
     * @return Closure
     */
    protected function carry(): Closure
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                try {
                    return $pipe($passable, $stack);
                } catch (Throwable $e) {
                    return $this->handleException($passable, $e);
                }
            };
        };
    }

    /**
     * 异常处理
     * @param mixed $passable
     * @param Throwable $e
     * @return mixed
     * @throws Throwable
     */
    protected function handleException(mixed $passable, Throwable $e): mixed
    {
        if ($this->exceptionHandler) {
            return call_user_func($this->exceptionHandler, $passable, $e);
        }
        throw $e;
    }
}