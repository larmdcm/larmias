<?php

declare(strict_types=1);

namespace LarmiasTest\Di\Classes;

use LarmiasTest\Di\Annotation\Props;
use LarmiasTest\Di\Annotation\Classes;
use LarmiasTest\Di\Annotation\Method;

#[Classes]
class User extends BaseUser
{
    #[Props]
    protected int $id = 1;

    #[Props]
    protected string $name = 'user';

    #[Props('user1'), Props('user2')]
    protected string $baseName = '2';

    #[Method]
    public function getId(): int
    {
        return $this->id;
    }
}