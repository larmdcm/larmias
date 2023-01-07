<?php

declare(strict_types=1);

namespace Larmias\Repository\Drivers\Think\Concerns;

use Larmias\Repository\Drivers\Think\EloquentDriver;
use ReflectionObject;

/**
 * @mixin EloquentDriver
 */
trait HasData
{
    /**
     * @var array
     */
    protected array $dataOpt = [];

    /**
     * 获取修改的数据
     *
     * @param array $data
     * @return array
     * @throws \Throwable
     * @throws \ReflectionException
     */
    protected function getUpdateData(array $data): array
    {
        $pk = $this->getPrimaryKey();
        if (isset($data[$pk])) {
            unset($data[$pk]);
        }
        $this->setUpdateDataTime($data);
        return $data;
    }

    /**
     * 设置修改时间
     *
     * @param array $data
     * @return void
     * @throws \ReflectionException
     * @throws \Throwable
     */
    protected function setUpdateDataTime(array &$data): void
    {
        $field = null;
        if (isset($this->dataOpt['updateTime'])) {
            $field = $this->dataOpt['updateTime'];
        } else {
            $autoWriteTimestamp = $this->getAutoWriteTimestamp();
            if ($autoWriteTimestamp) {
                $model = $this->repository->getModel();
                $refObject = new ReflectionObject($model);
                $updateTime = $refObject->getProperty('updateTime');
                if (!$updateTime->isPublic()) {
                    $updateTime->setAccessible(true);
                }
                $field = $updateTime->getValue($model);
            }
            $this->dataOpt['updateTime'] = $field;
        }
        if ($field && !isset($data[$field])) {
            $data[$field] = $this->getAutoWriteDateTime();
        }
    }

    /**
     * @return string|null
     * @throws \ReflectionException
     * @throws \Throwable
     */
    protected function getAutoWriteTimestamp(): ?string
    {
        if (isset($this->dataOpt['autoWriteTimestamp'])) {
            return $this->dataOpt['autoWriteTimestamp'];
        }
        $model = $this->repository->getModel();
        $refObject = new ReflectionObject($model);
        $refProperty = $refObject->getProperty('autoWriteTimestamp');
        if (!$refProperty->isPublic()) {
            $refProperty->setAccessible(true);
        }
        $field = $refProperty->getValue($model);
        if (!\is_string($field)) {
            $field = null;
        }
        return $this->dataOpt['autoWriteTimestamp'] = $field;
    }

    /**
     * @return int|string
     * @throws \ReflectionException
     * @throws \Throwable
     */
    protected function getAutoWriteDateTime(): int|string
    {
        $autoWriteTimestamp = $this->getAutoWriteTimestamp();
        return \in_array($autoWriteTimestamp, ['datetime', 'date', 'timestamp'], true) ? \date('Y-m-d H:i:s.u', \time()) : \time();
    }
}