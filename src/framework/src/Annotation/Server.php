<?php

declare(strict_types=1);

namespace Larmias\Framework\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Server
{
    /**
     * @param int $type
     * @param string|null $host
     * @param int|null $port
     * @param string|null $name
     * @param int|null $num
     * @param array $settings
     * @param bool|null $enableCoroutine
     * @param bool $enabled
     */
    public function __construct(
        public int     $type,
        public ?string $host = null,
        public ?int    $port = null,
        public ?string $name = null,
        public ?int    $num = null,
        public array   $settings = [],
        public ?bool   $enableCoroutine = null,
        public bool    $enabled = true,
    )
    {
    }
}