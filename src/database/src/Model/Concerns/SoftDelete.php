<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Concerns;

use Larmias\Database\Model\Contracts\QueryInterface;
use Larmias\Database\Model;
use Closure;
use function date;
use function time;

/**
 * @mixin Model
 */
trait SoftDelete
{
    /**
     * 是否包含软删除数据
     * @var bool
     */
    protected bool $withTrashed = false;

    /**
     * 软删除字段
     * @var string
     */
    protected string $softDeleteField = 'deleted';

    /**
     * 软删除默认值
     * @var string|int|null
     */
    protected string|int|null $softDeleteDefaultValue = 0;

    /**
     * 设置是否强制删除
     * @var bool
     */
    protected bool $forceDelete = false;

    /**
     * 判断当前实例是否被软删除
     * @return bool
     */
    public function trashed(): bool
    {
        $value = $this->getOriginValue($this->softDeleteField);
        if (is_null($this->softDeleteDefaultValue)) {
            return $value !== null;
        }
        return $value != $this->softDeleteDefaultValue;
    }

    /**
     * 查询包含软删除数据
     * @return QueryInterface
     */
    public static function withTrashed(): QueryInterface
    {
        return static::new()->withTrashedData(true)->newQuery();
    }

    /**
     * 查询包含软删除数据
     * @return QueryInterface
     */
    public function queryWithTrashed(): QueryInterface
    {
        return $this->withTrashedData(true)->newQuery();
    }

    /**
     * 只查询软删除数据
     * @return QueryInterface
     */
    public static function onlyTrashed(): QueryInterface
    {
        $model = new static();
        $query = $model->newQuery();
        $model->withInTrashed($query);
        return $query;
    }


    /**
     * 只查询软删除数据
     * @return QueryInterface
     */
    public function queryOnlyTrashed(): QueryInterface
    {
        $query = $this->newQuery();
        $this->withInTrashed($query);
        return $query;
    }

    /**
     * 删除
     * @return bool
     */
    public function delete(): bool
    {
        if (($this->trashed() && !$this->isForceDelete()) || !$this->isExists()) {
            return false;
        }

        return $this->whenFireEvent(['deleting', 'deleted'], function (Closure $before, Closure $after) {
            if (!$before()) {
                return false;
            }

            if ($this->isForceDelete()) {
                $result = $this->newQuery()->removeOptions('soft_delete')->delete() > 0;
            } else {
                $result = $this->save([
                    $this->softDeleteField => $this->getSoftDeleteValue(),
                ]);
            }

            $this->exists(false);
            $after();

            return $result;
        });
    }

    /**
     * 恢复被软删除的记录
     * @return bool
     */
    public function restore(): bool
    {
        if (!$this->trashed() || !$this->isExists()) {
            return false;
        }

        return $this->whenFireEvent(['restoring', 'restored'], function (Closure $before, Closure $after) {
            if (!$before()) {
                return false;
            }
            $data = [$this->softDeleteField => $this->softDeleteDefaultValue];
            $result = $this->save($data);
            $this->exists(true);
            $after();
            return $result;
        });
    }

    /**
     * 获取软删除字段值
     * @return string|int|null
     */
    protected function getSoftDeleteValue(): string|int|null
    {
        return match ($this->getSoftDeleteFieldType()) {
            'datetime' => date('Y-m-d H:i:s.u', time()),
            'timestamp' => time(),
            'integer', 'int' => 1,
            default => null,
        };
    }

    /**
     * 获取软删除字段类型
     * @return string
     */
    protected function getSoftDeleteFieldType(): string
    {
        return $this->cast[$this->softDeleteField] ?? 'integer';
    }

    /**
     * 查询排除软删除字段
     * @param QueryInterface $query
     * @return void
     */
    protected function withNoTrashed(QueryInterface $query): void
    {
        $softDeleteField = '${table}.' . $this->softDeleteField;
        $condition = $this->softDeleteDefaultValue === null ? ['null'] : ['=', $this->softDeleteDefaultValue];
        $query->useSoftDelete($softDeleteField, $condition);
    }

    /**
     * 查询软删除字段
     * @param QueryInterface $query
     * @return void
     */
    protected function withInTrashed(QueryInterface $query): void
    {
        $softDeleteField = '${table}.' . $this->softDeleteField;
        $condition = $this->softDeleteDefaultValue === null ? ['not null'] : ['<>', $this->softDeleteDefaultValue];
        $query->useSoftDelete($softDeleteField, $condition);
    }

    /**
     * 是否包含软删除数据
     * @param bool $withTrashed
     * @return self
     */
    protected function withTrashedData(bool $withTrashed): self
    {
        $this->withTrashed = $withTrashed;
        return $this;
    }

    /**
     * 是否强制删除
     * @return bool
     */
    public function isForceDelete(): bool
    {
        return $this->forceDelete;
    }

    /**
     * 设置强制删除
     * @param bool $forceDelete
     * @return Model|SoftDelete
     */
    public function forceDelete(bool $forceDelete = true): self
    {
        $this->forceDelete = $forceDelete;
        return $this;
    }
}