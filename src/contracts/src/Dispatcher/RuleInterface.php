<?php

declare(strict_types=1);

namespace Larmias\Contracts\Dispatcher;

interface RuleInterface
{
    /**
     * @return mixed
     */
    public function getHandler(): mixed;

    /**
     * @param string|null $name
     * @return mixed
     */
    public function getOption(?string $name = null): mixed;
}