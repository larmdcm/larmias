<?php

declare(strict_types=1);

namespace Larmias\Rpn;

class Utils
{
    /**
     * 计算操作符
     *
     * @var array|string[]
     */
    public static array $operator = ['(', '+', '-', '*', '/', ')'];

    /**
     * @param mixed $ch
     * @return bool
     */
    public static function isOperator(mixed $ch,bool $isFour = false): bool
    {
        static $fourOperator;
        if (!$ch) {
            return false;
        }
        $operator = static::$operator;
        if ($isFour) {
            if (!$fourOperator) {
                $fourOperator =  array_filter(static::$operator,fn($item) => !in_array($item,['(',')']));
            }
            $operator = $fourOperator;
        }
        return in_array($ch,$operator);
    }

    /**
     * @param mixed $ch
     * @return bool
     */
    public static function isLetter(mixed $ch): bool
    {
        return ctype_alnum($ch) || $ch == '_';
    }

    /**
     * @param mixed $ch
     * @return bool
     */
    public static function isNumber(mixed $ch): bool
    {
        return ctype_digit($ch);
    }
}