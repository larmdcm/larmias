<?php

declare(strict_types=1);

namespace Larmias\Trace\Collectors;

class DatabaseCollector extends BaseCollector
{
    public function beforeHandle(array $args): void
    {
        $this->data['sql'][] = ['sql' => $args['sql'], 'time' => $args['time']];
    }

    public function afterHandle(array $args): void
    {
    }
}