<?php

declare(strict_types=1);

namespace Larmias\Tests\Database\Model;

use Larmias\Database\Model\Contracts\QueryInterface;
use Larmias\Database\Model\Model;

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