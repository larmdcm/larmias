<?php

declare(strict_types=1);

namespace Larmias\Database\Connections;

class Schema
{
    /**
     * @var string
     */
    public const TYPE_STRING = 'string';

    /**
     * @var string
     */
    public const TYPE_FLOAT = 'float';

    /**
     * @var string
     */
    public const TYPE_INT = 'int';

    /**
     * @var string
     */
    public const TYPE_BOOL = 'bool';

    /**
     * @var string
     */
    public const TYPE_TIMESTAMP = 'timestamp';

    /**
     * @var string
     */
    public const TYPE_DATETIME = 'datetime';

    /**
     * @var string
     */
    public const TYPE_DATE = 'date';

    /**
     * @var array
     */
    public static array $cache = [];

    /**
     * 获取字段类型
     * @param string $type
     * @return string
     */
    public static function getFieldType(string $type): string
    {
        $result = 'string';
        if (0 === stripos($type, 'set') || 0 === stripos($type, 'enum')) {
            $result = self::TYPE_STRING;
        } elseif (preg_match('/(double|float|decimal|real|numeric)/is', $type)) {
            $result = self::TYPE_FLOAT;
        } elseif (preg_match('/(int|serial|bit)/is', $type)) {
            $result = self::TYPE_INT;
        } elseif (preg_match('/bool/is', $type)) {
            $result = self::TYPE_BOOL;
        } elseif (0 === stripos($type, 'timestamp')) {
            $result = self::TYPE_TIMESTAMP;
        } elseif (0 === stripos($type, 'datetime')) {
            $result = self::TYPE_DATETIME;
        } elseif (0 === stripos($type, 'date')) {
            $result = self::TYPE_DATE;
        }

        return $result;
    }
}