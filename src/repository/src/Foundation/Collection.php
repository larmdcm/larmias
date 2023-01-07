<?php

declare(strict_types=1);

namespace Larmias\Repository\Foundation;

use Larmias\Utils\Collection as BaseCollection;
use function Larmias\Utils\data_set;

class Collection extends BaseCollection
{
    /**
     * @var array
     */
    protected array $config = [];

    /**
     * @param array|string $key
     * @param mixed $value
     * @return self
     */
    public function setConfig(array|string $key, mixed $value): self
    {
        data_set($this->config, $key, $value);
        return $this;
    }

    /**
     * 输出树形数组
     *
     * @param string|int $parentId
     * @return array
     */
    public function toTree(string|int $parentId = 0): array
    {
        return Tree::getInstance($this->toArray(), $this->config['tree_options'] ?? [])->layer($parentId);
    }
}