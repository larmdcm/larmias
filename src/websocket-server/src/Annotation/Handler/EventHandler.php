<?php

declare(strict_types=1);

namespace Larmias\WebSocketServer\Annotation\Handler;

use Larmias\Contracts\Annotation\AnnotationHandlerInterface;
use Larmias\WebSocketServer\Contracts\EventInterface;

class EventHandler implements AnnotationHandlerInterface
{
    /**
     * @var array[]
     */
    protected static array $container = [
        'events' => [],
    ];

    /**
     * 初始化
     * @param EventInterface $event
     */
    public function __construct(protected EventInterface $event)
    {
    }

    public function handle(): void
    {
        foreach (static::$container['events'] as $events) {
            foreach ($events as $event) {
                $value = $event['value'][0];
                $this->event->on($value->event ?: $event['method'], [$event['class'], $event['method']]);
            }
        }
    }

    public function collect(array $param): void
    {
        if ($param['type'] == 'method') {
            static::$container['events'][$param['class']][] = $param;
        }
    }
}