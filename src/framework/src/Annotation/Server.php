<?php

declare(strict_types=1);

namespace Larmias\Framework\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Server
{
    /**
     * @param int|string $type
     * @param string|null $host
     * @param int|string|null $port
     * @param string|null $name
     * @param int|string|null $num
     * @param array $settings
     * @param bool|null $enableCoroutine
     * @param bool $enabled
     */
    public function __construct(
        public int|string       $type,
        public ?string          $host = null,
        public int|string|null  $port = null,
        public ?string          $name = null,
        public int|string|null  $num = null,
        public array            $settings = [],
        public bool|string|null $enableCoroutine = null,
        public bool|string      $enabled = true,
    )
    {
    }
}