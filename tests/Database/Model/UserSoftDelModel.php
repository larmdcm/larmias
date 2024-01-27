<?php

declare(strict_types=1);

namespace LarmiasTest\Database\Model;

use Larmias\Database\Model;
use Larmias\Database\Model\Concerns\SoftDelete;

/**
 * @property int $id
 * @property string $username
 * @property int $integral
 */
class UserSoftDelModel extends Model
{
    use SoftDelete;

    /**
     * @var string|null
     */
    protected ?string $table = 't_user';

    protected array $hidden = ['password'];

    protected array $guarded = ['status'];

    public function generateUniqueId(): string
    {
        return md5(uniqid());
    }
}