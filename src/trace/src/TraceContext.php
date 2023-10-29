<?php

declare(strict_types=1);

namespace Larmias\Trace;

use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ContextInterface;
use Larmias\Trace\Contracts\TraceContextInterface;
use Larmias\Trace\Contracts\TraceInterface;

class TraceContext implements TraceContextInterface
{
    /**
     * @param ContainerInterface $container
     * @param ContextInterface $context
     * @param ConfigInterface $config
     */
    public function __construct(protected ContainerInterface $container, protected ContextInterface $context, protected ConfigInterface $config)
    {
    }

    /**
     * @return TraceInterface
     */
    public function getContextForTrace(): TraceInterface
    {
        return $this->context->remember(TraceInterface::class, function () {
            $config = $this->config->get('trace.http', []);
            return $this->container->make(TraceInterface::class, ['config' => $config], true);
        });
    }
}