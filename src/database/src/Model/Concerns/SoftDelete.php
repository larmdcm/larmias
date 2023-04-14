<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Concerns;

use Larmias\Database\Contracts\QueryInterface;
use Larmias\Database\Model;
use RuntimeException;
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
            $result = $this->query()->where($this->getWhere())->delete() > 0;
        } else {
            $result = $this->save();
        }

        if ($result) {
            $this->setExists(false);
        }

        return $result;
    }

    /**
     * @return string|int
     */
    protected function getSoftDeleteValue(): string|int
    {
        return match ($this->getSoftDeleteFieldType()) {
            'datetime' => date('Y-m-d H:i:s.u', time()),
            'timestamp' => time(),
            'integer', 'int' => 1,
            default => throw new RuntimeException("getSoftDeleteData value parse error"),
        };
    }

    /**
     * @return string
     */
    protected function getSoftDeleteFieldType(): string
    {
        return $this->cast[$this->softDeleteField] ?? 'integer';
    }

    /**
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
     * @return bool
     */
    public function isForce(): bool
    {
        return $this->force;
    }

    /**
     * @param bool $force
     */
    public function force(bool $force): void
    {
        $this->force = $force;
    }
}