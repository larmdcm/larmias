<?php

declare(strict_types=1);

namespace Larmias\VarDumper\Dumper;

class CliDumper extends Dumper
{
    /**
     * @param ...$vars
     * @return string
     */
    public function dump(...$vars): string
    {
        return PHP_EOL . $this->getVarDumpString(...$vars) . PHP_EOL;
    }
}