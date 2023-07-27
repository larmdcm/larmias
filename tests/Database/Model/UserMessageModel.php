<?php

declare(strict_types=1);

namespace Larmias\Tests\Database\Model;

use Larmias\Database\Model\AbstractModel;

class UserMessageModel extends AbstractModel
{
    protected ?string $name = 'user_message';
}