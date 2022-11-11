<?php

class A
{
    public function echo()
    {
        echo A::class . PHP_EOL;
    }
}

class B
{
    public function __construct(protected A $a)
    {
    }

    public function echo()
    {
        $this->a->echo();
    }
}