<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Concerns;

use Swoole\Atomic;

trait WithIdAtomic
{
    protected Atomic $idAtomic;

    public function initIdAtomic(): void
    {
        $this->idAtomic = new Atomic();
    }

    public function generateId(): int
    {
        return $this->idAtomic->add();
    }
}