<?php

declare(strict_types=1);

namespace LarmiasTest\Di\Classes;

use LarmiasTest\Di\Annotation\Props;
use LarmiasTest\Di\Annotation\Method;
use LarmiasTest\Di\Annotation\ParentClasses;

#[ParentClasses]
class BaseUser
{
    #[Props]
    protected string $baseName = 'base_user';

    #[Method]
    public function getBaseName(): string
    {
        return $this->baseName;
    }
}