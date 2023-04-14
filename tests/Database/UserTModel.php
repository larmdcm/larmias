<?php

declare(strict_types=1);

namespace Larmias\Tests\Database;

use Larmias\Database\Model;
use Larmias\Database\Model\Concerns\SoftDelete;
use Larmias\Database\Model\Concerns\HasUuid;

/**
 * @property int $id
 * @property string $username
 * @property int $integral
 */
class UserTModel extends Model
{
    use SoftDelete;
    use HasUuid;

    /**
     * @var string|null
     */
    protected ?string $table = 't_user';
}