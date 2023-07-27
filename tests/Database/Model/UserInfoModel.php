<?php

declare(strict_types=1);

namespace Larmias\Tests\Database\Model;

use Larmias\Database\Model\AbstractModel;
use Larmias\Database\Model\Relations\BelongsTo;

/**
 * @property int $user_id
 * @property int $id
 * @property int $age
 * @property string $address
 */
class UserInfoModel extends AbstractModel
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

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id', 'id');
    }
}