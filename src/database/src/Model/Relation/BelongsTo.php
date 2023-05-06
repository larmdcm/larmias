<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Relation;

use Larmias\Database\Model;

class BelongsTo extends OneToOne
{
    /**
     * @return void
     */
    public function initModel(): void
    {
        $this->model->where($this->getLocalKey(), $this->parent->getAttribute($this->getForeignKey()));
    }

    /**
     * @return Model|null
     */
    public function getRelation(): ?Model
    {
        return $this->getModel()->first();
    }
}