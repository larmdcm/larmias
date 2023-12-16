<?php

declare(strict_types=1);

namespace LarmiasTest\Database\Model;

use Larmias\Database\Model;
use Larmias\Database\Model\Contracts\QueryInterface;

class UserScopeModel extends Model
{
    protected ?string $table = 't_user';

    /**
     * @param QueryInterface $query
     * @param string $value
     * @return void
     */
    public function scopeUsername(QueryInterface $query, string $value): void
    {
        $query->where('username', $value);
    }
}