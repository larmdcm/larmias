<?php

declare(strict_types=1);

namespace Larmias\Tests\Database;

use Larmias\Database\Model;
use Larmias\Database\Model\Concerns\SoftDelete;

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
        return uniqid();
    }
}