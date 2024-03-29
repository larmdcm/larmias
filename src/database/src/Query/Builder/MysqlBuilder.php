<?php

declare(strict_types=1);

namespace Larmias\Database\Query\Builder;

use function explode;
use function is_numeric;
use function str_contains;
use function str_ends_with;

class MysqlBuilder extends Builder
{
    /**
     * 转义字段
     * @param string $field
     * @return string
     */
    public function escapeField(string $field): string
    {
        if (!$field || is_numeric($field) || $field == '*' || str_ends_with($field, ')')) {
            return (string)$field;
        }

        $field = trim($field);
        $table = '';
        $alias = '';
        $separator = '';

        $separators = [' AS ', ' as ', ' '];
        foreach ($separators as $item) {
            if (str_contains($field, $item)) {
                $separator = $item;
                [$field, $alias] = explode($item, $field);
            }
        }

        if (str_contains($field, '.')) {
            [$table, $field] = explode('.', $field);
        }

        if ($field != '*') {
            $field = $this->escapeString($field);
        }

        if ($table) {
            $field = $this->escapeString($table) . '.' . $field;
        }

        if ($alias) {
            $field = $field . $separator . $this->escapeString($alias);
        }

        return $field;
    }

    /**
     * 字符串转义
     * @param string $string
     * @return string
     */
    public function escapeString(string $string): string
    {
        return '`' . $string . '`';
    }
}