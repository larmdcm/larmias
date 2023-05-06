<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Relation;

use Larmias\Database\Model;

class HasOne extends OneToOne
{
    /**
     * @return void
     */
    protected function initModel(): void
    {
        $this->model->where($this->getForeignKey(), $this->parent->getAttribute($this->getLocalKey()));
    }

    /**
     * @return Model|null
     */
    public function getRelation(): ?Model
    {
        return $this->getModel()->first();
    }
}