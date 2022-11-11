<?php

declare(strict_types=1);

namespace Larmias\Event\Contracts;

interface ListenerInterface
{
    /**
     * @return string[]
     */
    public function listen(): array;

    /**
     * @param object $event
     * @return void
     */
    public function process(object $event): void;
}