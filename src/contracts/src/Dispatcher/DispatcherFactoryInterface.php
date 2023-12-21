<?php

declare(strict_types=1);

namespace Larmias\Contracts\Dispatcher;

interface DispatcherFactoryInterface
{
    /**
     * @param RuleInterface $rule
     * @return DispatcherInterface
     */
    public function make(RuleInterface $rule): DispatcherInterface;
}