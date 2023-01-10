<?php

declare(strict_types=1);

namespace Larmias\Repository\Drivers\Think\Concerns;

use Larmias\Repository\Drivers\Think\EloquentDriver;
use Larmias\Repository\Models\Think\Concerns\BooleanSoftDelete;

/**
 * @mixin EloquentDriver
 */
trait SoftDelete
{
    /**
     * 是否软删除
     *
     * @return bool
     */
    protected function isSoftDelete(): bool
    {
        $traitList = \class_uses_recursive($this->repository->newModel());
        return \in_array(BooleanSoftDelete::class, $traitList, true);
    }

    /**
     * 获取软删除字段数据
     *
     * @return array
     * @throws \Throwable
     */
    protected function getDeleteData(): array
    {
        return [$this->getDeleteField() => 1];
    }

    /**
     * 获取软删除字段
     *
     * @return string
     * @throws \ReflectionException
     * @throws \Throwable
     */
    protected function getDeleteField(): string
    {
        if (isset($this->data['deleteTime'])) {
            return $this->data['deleteTime'];
        }
        $model = $this->repository->newModel();
        $refObject = new \ReflectionObject($model);
        $refProperty = $refObject->getProperty('deleteTime');
        if (!$refProperty->isPublic()) {
            $refProperty->setAccessible(true);
        }
        $field = $refProperty->getValue($model);
        if (!$field) {
            throw new \RuntimeException('The soft deletion field is incorrect');
        }
        return $this->data['deleteTime'] = $field;
    }

    /**
     * @param mixed $condition
     * @return int
     * @throws \Throwable
     */
    protected function softDelete($condition): int
    {
        return $this->update($this->getDeleteData(), $condition);
    }
}