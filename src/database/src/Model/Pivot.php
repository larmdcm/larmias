<?php

declare(strict_types=1);

namespace Larmias\Database\Model;

class Pivot extends Model
{
    /**
     * 是否自动写入时间
     * @var bool
     */
    protected bool $autoWriteTimestamp = false;

    /**
     * 初始化
     * @param array $data
     * @param string|null $name
     */
    public function __construct(array $data = [], ?string $name = null)
    {
        if ($this->name === null) {
            $this->name = $name;
        }

        parent::__construct($data);
    }
}