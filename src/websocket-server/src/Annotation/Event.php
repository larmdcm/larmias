<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Event
{
    public function __construct(public string $event = 'event')
    {
    }
}