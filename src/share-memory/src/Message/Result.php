<?php

declare(strict_types=1);

namespace Larmias\ShareMemory\Message;

class Result
{
    public function __construct(public mixed $data)
    {
    }
}