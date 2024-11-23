<?php

declare(strict_types=1);

namespace Larmias\Di\Annotation\Handler;

use Larmias\Contracts\ContainerInterface;
use Larmias\Di\Annotation\Dependence;

class DependenceAnnotationHandler extends AbstractAnnotationHandler
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function collect(array $param): void
    {
        /** @var Dependence $value */
        $value = $param['value'][0];
        call_user_func([$this->container, $value->force ? 'bind' : 'bindIf'], $value->provider ?: $param['class'], $param['class']);
    }

    public function handle(): void
    {
    }
}