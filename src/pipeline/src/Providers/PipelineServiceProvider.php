<?php

declare(strict_types=1);

namespace Larmias\Pipeline\Providers;

use Larmias\Contracts\PipelineInterface;
use Larmias\Framework\ServiceProvider;
use Larmias\Pipeline\Pipeline;

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