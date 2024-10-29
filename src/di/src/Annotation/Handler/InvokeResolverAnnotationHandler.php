<?php

declare(strict_types=1);

namespace Larmias\Di\Annotation\Handler;

use Larmias\Contracts\Annotation\AnnotationHandlerInterface;
use Larmias\Di\Annotation\Invoke;
use Larmias\Di\Invoker\InvokeResolver;

class InvokeResolverAnnotationHandler implements AnnotationHandlerInterface
{
    /**
     * @var array
     */
    protected static array $list = [];

    /**
     * @param array $param
     * @return void
     */
    public function collect(array $param): void
    {
        if ($param['annotation'] !== Invoke::class) {
            return;
        }

        static::$list[] = $param;
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        foreach (static::$list as $item) {
            InvokeResolver::collect($item);
        }
    }
}