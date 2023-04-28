<?php

declare(strict_types=1);

namespace Larmias\Tests\Database;

use Larmias\Database\Model;
use Larmias\Database\Model\Relation\HasOne;

/**
 * @property int $id
 * @property UserInfoModel $userInfo
 * @property string $username
 * @property int $integral
 */
class UserModel extends Model
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
    protected ?string $table = 't_user';

    /**
     * @param string $value
     * @return string
     */
    public function setPasswordAttr(string $value): string
    {
        return md5($value);
    }

    /**
     * @param mixed $value
     * @return int
     */
    public function getIntegralAttr(mixed $value): int
    {
        $value = (int)$value;
        return $value > 0 ? $value : -1;
    }

    /**
     * @return HasOne
     */
    public function userInfo(): HasOne
    {
        return $this->hasOne(UserInfoModel::class, 'user_id', 'id');
    }
}