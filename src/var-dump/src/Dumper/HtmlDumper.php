<?php

declare(strict_types=1);

namespace Larmias\VarDumper\Dumper;

use function htmlspecialchars;
use function extension_loaded;
use const ENT_SUBSTITUTE;

class HtmlDumper extends Dumper
{
    /**
     * @param ...$vars
     * @return string
     */
    public function dump(...$vars): string
    {
        $contents = $this->getVarDumpString(...$vars);
        if (!extension_loaded('xdebug')) {
            $contents = htmlspecialchars($contents, ENT_SUBSTITUTE);
        }
        return '<pre>' . $contents . '</pre>';
    }
}