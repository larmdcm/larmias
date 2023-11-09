<?php

declare(strict_types=1);

namespace Larmias\Support;

class ResourceGenerator
{
    /**
     * @param string $body
     * @param string $filename
     * @return resource
     */
    public static function from(string $body, string $filename = 'php://temp')
    {
        $resource = fopen($filename, 'r+');
        if ($body !== '') {
            fwrite($resource, $body);
            fseek($resource, 0);
        }

        return $resource;
    }

    /**
     * @param string $body
     * @return resource
     */
    public static function fromMemory(string $body)
    {
        return static::from($body, 'php://memory');
    }
}
