<?php

declare(strict_types=1);

namespace Larmias\Tests\Database;

use Larmias\Database\Model;

/**
 * @property string $username
 */
class UserModel extends Model
{
    /**
     * @var string|null
     */
    protected ?string $table = 't_user';

    public function setPasswordAttr(string $value): string
    {
        return md5($value);
    }
}