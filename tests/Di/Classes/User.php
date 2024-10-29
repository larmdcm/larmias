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

    public function __construct(int $id = 1)
    {
        $this->id = $id;
    }

    #[Method]
    public function getId(): int
    {
        return $this->id;
    }

    public static function findById(int|string $id): User
    {
        return new User($id);
    }

    public function showId(): void
    {
        \Larmias\Support\println((string)$this->id);
    }

    public function showList()
    {
        \Larmias\Support\println('1');
        \Larmias\Support\println('2');
    }
}