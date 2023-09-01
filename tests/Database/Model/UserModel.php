<?php

declare(strict_types=1);

namespace LarmiasTest\Database\Model;

use Larmias\Database\Model\Model;
use Larmias\Database\Model\Relations\BelongsToMany;
use Larmias\Database\Model\Relations\HasMany;
use Larmias\Database\Model\Relations\HasOne;

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

    /**
     * @return HasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(UserMessageModel::class, 'user_id', 'id');
    }

    /**
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(RoleModel::class, 'user_role', 'role_id', 'user_id');
    }
}