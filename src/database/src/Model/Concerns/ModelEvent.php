<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Concerns;

use Larmias\Database\Model\Model;
use Larmias\Stringable\Str;

trait ModelEvent
{
    /**
     * 是否需要事件响应
     * @var bool
     */
    protected bool $withEvent = true;

    /**
     * 设置当前操作是否需要事件响应
     * @param bool $withEvent
     * @return Model|ModelEvent
     */
    public function withEvent(bool $withEvent): self
    {
        $this->withEvent = $withEvent;
        return $this;
    }

    /**
     * 触发事件
     * @param string $event
     * @return bool
     */
    public function fireEvent(string $event): bool
    {
        $method = 'on' . Str::studly($event);

        $result = method_exists(static::class, $method) ? call_user_func([static::class, $method], $this) : true;

        return !($result === false);
    }
}