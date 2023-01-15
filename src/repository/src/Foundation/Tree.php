<?php

declare(strict_types=1);

namespace Larmias\Repository\Foundation;

class Tree
{
    /**
     * 生成树型结构所需要的2维数组
     *
     * @var array
     */
    protected array $data = [];

    /**
     * 配置参数
     *
     * @var array
     */
    protected array $options = [
        // 主键
        'primary_key' => 'id',
        // 父键
        'parent_key' => 'parent_id',
        // 孩子节点
        'children_key' => 'children',
        // level key
        'level_key' => 'level',
        // icon key
        'icon_key' => 'icon',
        // icon
        'icon' => '|--',
    ];

    /**
     * 构造方法
     *
     * @param array|object $data
     * @param array $options
     */
    public function __construct(array|object $data = [], array $options = [])
    {
        $this->data($data)->options($options);
    }

    /**
     * make tree object
     *
     * @param array|object $data
     * @param array $options
     * @return Tree
     */
    public static function make(array|object $data = [], array $options = []): Tree
    {
        return new static($data, $options);
    }

    /**
     * 设置数据
     *
     * @param array|object $data
     * @return Tree
     */
    public function data(array|object $data = []): self
    {
        $this->data = $this->convertToArray($data);
        return $this;
    }

    /**
     * 设置配置选项
     *
     * @param array $options
     * @return Tree
     */
    public function options(array $options = []): self
    {
        $this->options = \array_merge($this->options, $options);
        return $this;
    }


    /**
     * 得到子级数组
     *
     * @param string|int $id
     * @return array
     */
    public function child(string|int $id): array
    {
        $result = [];
        foreach ($this->data as $item) {
            if ($item[$this->options['primary_key']] == $id) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * 层级树形结构
     *
     * @param string|int $parentId
     * @return array
     */
    public function layer(string|int $parentId = 0): array
    {
        $result = [];

        foreach ($this->data as $item) {
            if (!isset($item[$this->options['parent_key']]) || !isset($item[$this->options['primary_key']])) {
                continue;
            }
            if ($item[$this->options['parent_key']] == $parentId) {
                $item[$this->options['children_key']] = $this->layer($item[$this->options['primary_key']]);
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * 层级树形结构等级
     *
     * @param string|int $parentId
     * @param integer $maxLevel
     * @param integer $level
     * @param array $result
     * @return array
     */
    public function layerLevel(string|int $parentId = 0, int $maxLevel = 0, int $level = 0, array $result = []): array
    {
        foreach ($this->data as $item) {
            if ($maxLevel > 0 && $maxLevel == $level) {
                return $result;
            }
            if (!isset($item[$this->options['parent_key']]) || !isset($item[$this->options['primary_key']])) {
                continue;
            }
            if ($item[$this->options['parent_key']] == $parentId) {
                $item[$this->options['level_key'] ?? 'level'] = $level;
                $item[$this->options['icon_key'] ?? 'icon'] = str_repeat($this->options['icon'], $level);
                $result[] = $item;
                $result = $this->layerLevel($item[$this->options['primary_key']], $maxLevel, $level + 1, $result);
            }
        }

        return $result;
    }

    /**
     * 转换成数组
     *
     * @param mixed $data
     * @return array
     */
    protected function convertToArray(mixed $data): array
    {
        if (is_object($data)) {
            if (method_exists($data, 'toArray')) {
                return $data->toArray();
            }
        }
        return (array)$data;
    }
}