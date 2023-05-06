<?php

declare(strict_types=1);

namespace Larmias\Database\Query\Builder;

use function explode;
use function sprintf;
use function str_contains;

class MysqlBuilder extends Builder
{
    /**
     * @param string $str
     * @return string
     */
    public function escape(string $str): string
    {
        $str = trim($str);
        $separator = ['.', ' AS ', ' '];
        foreach ($separator as $item) {
            if (str_contains($str, $item)) {
                $strSplit = explode($item, $str);
                return sprintf('`%s`%s`%s`', $strSplit[0], $item, $strSplit[1]);
            }
        }
        return sprintf('`%s`', $str);
    }
}