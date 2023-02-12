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
     * 返回树形结构
     *
     * @param string|int $parentId
     * @return static
     */
    public function toTree(string|int $parentId = 0): static
    {
        return new static(Tree::make($this->toArray(), $this->config['tree_options'] ?? [])->layer($parentId));
    }

    /**
     * 返回树形结构level
     *
     * @param string|int $parentId
     * @return static
     */
    public function toTreeLevel(string|int $parentId = 0): static
    {
        return new static(Tree::make($this->toArray(), $this->config['tree_options'] ?? [])->layerLevel($parentId));
    }
}