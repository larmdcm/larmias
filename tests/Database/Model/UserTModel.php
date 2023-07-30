<?php

declare(strict_types=1);

namespace Larmias\Tests\Database\Model;

use Larmias\Database\Model\Concerns\SoftDelete;
use Larmias\Database\Model\Model;

/**
 * @property int $id
 * @property string $username
 * @property int $integral
 */
class UserTModel extends Model
{
    use SoftDelete;

    /**
     * @var string|null
     */
    protected ?string $table = 't_user';

    public function generateUniqueId(): string
    {
        return md5(uniqid());
    }
}