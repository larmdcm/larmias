<?php

declare(strict_types=1);

namespace LarmiasTest\Di\Classes;

use LarmiasTest\Di\Annotation\Invoker;

class UserInfo
{
    #[Invoker]
    public function getUserInfo(): array
    {
        return [
            'id' => 1,
        ];
    }
}