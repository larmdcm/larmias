<?php

declare(strict_types=1);

namespace Larmias\Pipeline;

use Larmias\Contracts\PipelineInterface;
use Larmias\Framework\ServiceProvider;

class PipelineServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->container->bindIf([
            PipelineInterface::class => Pipeline::class,
        ]);
    }
}