<?php

declare(strict_types=1);

namespace Larmias\Routing;

class Dispatched
{
    /**
     * Result constructor.
     * @param int $status
     * @param Rule|null $rule
     * @param array $params
     */
    public function __construct(public int $status, public ?Rule $rule = null, public array $params = [])
    {
    }
}