<?php

declare(strict_types=1);

namespace Larmias\View\Drivers\Blade\Support;

use Stringable;

class HtmlString implements Stringable
{
    /**
     * The HTML string.
     *
     * @var string
     */
    protected string $html;

    /**
     * Create a new HTML string instance.
     *
     * @param string $html
     * @return void
     */
    public function __construct(string $html)
    {
        $this->html = $html;
    }

    /**
     * Get the HTML string.
     *
     * @return string
     */
    public function toHtml(): string
    {
        return $this->html;
    }

    /**
     * Get the HTML string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toHtml();
    }
}
