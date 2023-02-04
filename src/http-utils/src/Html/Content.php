<?php

declare(strict_types=1);

namespace Larmias\Http\Utils\Html;

use Larmias\Utils\Str;

class Content
{
    /**
     * @var string
     */
    protected string $char;

    /**
     * @var int
     */
    protected int $len;

    /**
     * @var int
     */
    protected int $pos;

    /**
     * Content constructor.
     * @param string $content
     */
    public function __construct(protected string $content)
    {
        $this->len = Str::length($this->content);
        $this->pos = 0;
    }

    public function char(): string
    {
        return $this->content[$this->pos] ?? '';
    }
}