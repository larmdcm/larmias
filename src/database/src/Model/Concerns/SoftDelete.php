<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Concerns;

use Larmias\Database\Contracts\QueryInterface;
use Larmias\Database\Model\Model;
use function date;
use function time;

/**
 * @mixin Model
 */
trait SoftDelete
{
    /**
     * @var string
     */
    protected string $softDeleteField = 'deleted';

    /**
     * @var string|int|null
     */
    protected string|int|null $softDeleteValue = 0;

    /**
     * @var bool
     */
    protected bool $force = false;

    /**
     * 删除
     * @return bool
     */
    public function delete(): bool
    {
        if (!$this->isExists()) {
            return false;
        }

        $data = [
            $this->softDeleteField => $this->getSoftDeleteValue(),
        ];

        $this->setAttributes($data);

        if ($this->isForce()) {
            $result = $this->query()->removeOptions('soft_delete')->where($this->getWhere())->delete() > 0;
        } else {
            $result = $this->save();
        }

        if ($result) {
            $this->setExists(false);
        }

        return $result;
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
        $softDeleteField = $query->getTable() . '.' . $this->softDeleteField;
        $condition = $this->softDeleteValue === null ? ['null'] : ['=', $this->softDeleteValue];
        $query->useSoftDelete($softDeleteField, $condition);
    }

    /**
     * 是否强制删除
     * @return bool
     */
    public function isForce(): bool
    {
        return $this->force;
    }

    /**
     * 设置强制删除
     * @param bool $force
     * @return Model|SoftDelete
     */
    public function force(bool $force): self
    {
        $this->force = $force;
        return $this;
    }
}