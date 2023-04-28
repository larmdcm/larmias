<?php

declare(strict_types=1);

namespace Larmias\Tests\Database;

use Larmias\Database\Model;

/**
 * @property int $user_id
 * @property int $id
 * @property int $age
 * @property string $address
 */
class UserInfoModel extends Model
{
    /**
     * @var array|string[]
     */
    protected array $cast = [
        'integral' => 'int',
    ];

    /**
     * @var string|null
     */
    protected ?string $table = 't_user_info';
}