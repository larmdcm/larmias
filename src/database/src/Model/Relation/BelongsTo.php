<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Relation;

use Larmias\Database\Model;

class BelongsTo extends OneToOne
{
    /**
     * 初始化模型查询
     * @return void
     */
    public function initModel(): void
    {
        $this->model->where($this->getLocalKey(), $this->parent->getAttribute($this->getForeignKey()));
    }

    /**
     * 获取关联数据
     * @return Model|null
     */
    public function getRelation(): ?Model
    {
        return $this->getModel()->first();
    }
}