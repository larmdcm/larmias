<?php

declare(strict_types=1);

namespace Larmias\Database\Contracts;

use ArrayAccess;

interface ModelInterface extends ArrayAccess
{
    /**
     * 实例化模型
     * @param array $data
     * @return ModelInterface
     */
    public static function new(array $data = []): ModelInterface;
}