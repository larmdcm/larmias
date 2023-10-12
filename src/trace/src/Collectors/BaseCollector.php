<?php

declare(strict_types=1);

namespace Larmias\Trace\Collectors;

use Larmias\Trace\Contracts\CollectorInterface;

abstract class BaseCollector implements CollectorInterface
{
    /**
     * @var array
     */
    protected array $data = [];

    /**
     * 收集数据
     * @param mixed $data
     * @return void
     */
    public function collect(mixed $data): void
    {
        $this->data = array_merge($this->data, $data);
    }

    /**
     * 获取收集的全部数据
     * @return array
     */
    public function all(): array
    {
        return $this->data;
    }
}