<?php

declare(strict_types=1);

namespace Larmias\Di\Annotation\Handler;

use Larmias\Contracts\Annotation\AnnotationHandlerInterface;
use Larmias\Di\Annotation\Aspect;
use Larmias\Di\Aop\AspectCollector;

class AspectAnnotationHandler implements AnnotationHandlerInterface
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
        if ($param['annotation'] !== Aspect::class) {
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
            AspectCollector::collect($item);
        }
    }
}