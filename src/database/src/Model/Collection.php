<?php

declare(strict_types=1);

namespace Larmias\Database\Model;

use Larmias\Collection\Collection as BaseCollection;
use Larmias\Database\Model\Contracts\CollectionInterface;
use Larmias\Database\Model;

class Collection extends BaseCollection implements CollectionInterface
{
    /**
     * 批量更新数据
     * @param array $data
     * @return bool
     */
    public function update(array $data = []): bool
    {
        $this->each(fn(Model $model) => $model->save($data));
        return true;
    }

    /**
     * 批量删除数据
     * @return bool
     */
    public function delete(): bool
    {
        $this->each(fn(Model $model) => $model->delete());
        return true;
    }

    /**
     * 批量设置隐藏的属性
     * @param array $hidden
     * @param bool $merge
     * @return self
     */
    public function hidden(array $hidden = [], bool $merge = false): self
    {
        $this->each(fn(Model $model) => $model->hidden($hidden, $merge));
        return $this;
    }

    /**
     * 批量设置输出的属性
     * @param array $visible
     * @param bool $merge
     * @return self
     */
    public function visible(array $visible = [], bool $merge = false): self
    {
        $this->each(fn(Model $model) => $model->visible($visible, $merge));
        return $this;
    }

    /**
     * 批量设置追加的属性
     * @param array $append
     * @param bool $merge
     * @return self
     */
    public function append(array $append = [], bool $merge = false): self
    {
        $this->each(fn(Model $model) => $model->append($append, $merge));
        return $this;
    }

    /**
     * 批量设置属性映射
     * @param array $mapping
     * @return self
     */
    public function mapping(array $mapping = []): self
    {
        $this->each(fn(Model $model) => $model->mapping($mapping));
        return $this;
    }

    /**
     * 批量设置属性命名转换
     * @param int|null $convertNameType
     * @return self
     */
    public function convertNameType(?int $convertNameType = null): self
    {
        $this->each(fn(Model $model) => $model->convertNameType($convertNameType));
        return $this;
    }
}