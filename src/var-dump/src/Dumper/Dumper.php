<?php

declare(strict_types=1);

namespace Larmias\VarDumper\Dumper;

use Larmias\VarDumper\Contracts\DumperInterface;
use function ob_start;
use function var_dump;
use function ob_get_clean;
use function preg_replace;

abstract class Dumper implements DumperInterface
{
    /**
     * @param string $type
     * @return DumperInterface
     */
    public static function make(string $type = 'html'): DumperInterface
    {
        $class = match ($type) {
            'html' => HtmlDumper::class,
            default => CliDumper::class,
        };

        return new $class;
    }

    /**
     * @param ...$vars
     * @return string
     */
    public function getVarDumpString(...$vars): string
    {
        ob_start();
        var_dump(...$vars);

        $output = ob_get_clean();
        return preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
    }
}