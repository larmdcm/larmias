<?php

declare(strict_types=1);

namespace Larmias\Validation;

use Larmias\Validation\Exceptions\RuleException;
use Larmias\Utils\Str;

/**
 * @method static bool required(mixed $value)
 * @method static bool accepted(mixed $value)
 * @method static bool date(mixed $value)
 * @method static bool activeUrl(mixed $value)
 * @method static bool boolean(mixed $value)
 * @method static bool number(mixed $value)
 * @method static bool alphaNum(mixed $value)
 * @method static bool array(mixed $value)
 * @method static bool mobile(mixed $value)
 * @method static bool idCard(mixed $value)
 * @method static bool zip(mixed $value)
 * @method static bool email(mixed $value)
 * @method static bool ip(mixed $value)
 * @method static bool integer(mixed $value)
 * @method static bool url(mixed $value)
 * @method static bool macAddr(mixed $value)
 * @method static bool float(mixed $value)
 */
class Validate
{
    /**
     * @var array|string[]
     */
    protected static array $regex = [
        'mobile' => '/^1[3-9]\d{9}$/',
        'idCard' => '/(^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$)|(^[1-9]\d{5}\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}$)/',
        'zip' => '/\d{6}/',
    ];

    /**
     * @var array
     */
    protected static array $filter = [
        'email' => FILTER_VALIDATE_EMAIL,
        'ip' => [FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6],
        'integer' => FILTER_VALIDATE_INT,
        'url' => FILTER_VALIDATE_URL,
        'macAddr' => FILTER_VALIDATE_MAC,
        'float' => FILTER_VALIDATE_FLOAT,
    ];

    /**
     * 验证字段值是否为有效格式
     *
     * @param mixed $value
     * @param string $rule
     * @return bool
     */
    public static function is(mixed $value, string $rule): bool
    {
        switch (Str::camel($rule)) {
            case 'required':
                // 必须
                $result = !empty($value) || \is_numeric($value);
                break;
            case 'accepted':
                // 接受
                $result = \in_array($value, ['1', 'on', 'yes']);
                break;
            case 'date':
                // 是否是一个有效日期
                $result = false !== \strtotime($value);
                break;
            case 'activeUrl':
                // 是否为有效的网址
                $result = checkdnsrr($value);
                break;
            case 'boolean':
            case 'bool':
                // 是否为布尔值
                $result = \in_array($value, [true, false, 0, 1, '0', '1'], true);
                break;
            case 'number':
                $result = ctype_digit((string)$value);
                break;
            case 'alphaNum':
                $result = ctype_alnum($value);
                break;
            case 'array':
                // 是否为数组
                $result = \is_array($value);
                break;
            default:
                if (function_exists('ctype_' . $rule)) {
                    // ctype验证规则
                    $ctypeFun = 'ctype_' . $rule;
                    $result = $ctypeFun($value);
                } elseif (isset(static::$filter[$rule])) {
                    // Filter_var验证规则
                    $result = static::filter($value, static::$filter[$rule]);
                } else if (isset(static::$regex[$rule])) {
                    // 正则验证
                    $result = static::regex($value, static::$regex[$rule]);
                } else {
                    throw new RuleException('The validation rule does not exist: ' . $rule);
                }
        }

        return $result;
    }


    /**
     * @param mixed $value
     * @param string $rule
     * @return bool
     */
    public static function filter(mixed $value, string|array $rule): bool
    {
        if (\is_string($rule) && \strpos($rule, ',')) {
            [$rule, $param] = \explode(',', $rule);
        } elseif (\is_array($rule)) {
            $param = $rule[1] ?? 0;
            $rule = $rule[0];
        } else {
            $param = 0;
        }
        return false !== \filter_var($value, \is_int($rule) ? $rule : \filter_id($rule), $param);
    }

    /**
     * @param mixed $value
     * @param string $rule
     * @return bool
     */
    public static function regex(mixed $value, string $rule): bool
    {
        if (\is_string($rule) && !\str_starts_with($rule, '/') && !\preg_match('/\/[imsU]{0,4}$/', $rule)) {
            // 不是正则表达式则两端补上/
            $rule = '/^' . $rule . '$/';
        }

        return \is_scalar($value) && 1 === \preg_match($rule, (string)$value);
    }

    /**
     * @param mixed $value
     * @param int $rule
     * @return bool
     */
    public static function max(mixed $value, int $rule): bool
    {
        return static::getSize($value) <= $rule;
    }

    /**
     * @param mixed $value
     * @param int $rule
     * @return bool
     */
    public static function min(mixed $value, int $rule): bool
    {
        return static::getSize($value) >= $rule;
    }

    /**
     * @param mixed $value
     * @param string|array $rule
     * @return bool
     */
    public static function in(mixed $value, string|array $rule): bool
    {
        return \in_array($value, \is_array($rule) ? $rule : \explode(',', $rule));
    }

    /**
     * @param mixed $value
     * @param string|array $rule
     * @return bool
     */
    public static function notIn(mixed $value, string|array $rule): bool
    {
        return !static::in($value, $rule);
    }

    /**
     * @param mixed $value
     * @param string|array $rule
     * @return bool
     */
    public static function between(mixed $value, string|array $rule): bool
    {
        if (\is_string($rule)) {
            $rule = \explode(',', $rule);
        }
        [$min, $max] = $rule;
        $value = static::getSize($value);
        return $value >= $min && $value <= $max;
    }

    /**
     * @param mixed $value
     * @param string|array $rule
     * @return bool
     */
    public static function notBetween(mixed $value, string|array $rule): bool
    {
        return !static::between($value, $rule);
    }

    /**
     * @param mixed $value
     * @param string|array $rule
     * @return bool
     */
    public static function length(mixed $value, mixed $rule): bool
    {
        return static::getSize($value) === static::getSize($rule);
    }

    /**
     * @param mixed $value
     * @return int
     */
    protected static function getSize(mixed $value): int
    {
        if (\is_array($value)) {
            $value = count($value);
        } else if (\is_string($value)) {
            $value = mb_strlen($value);
        }
        return (int)$value;
    }

    /**
     * @param string $name
     * @param array $args
     * @return bool
     */
    public static function __callStatic(string $name, array $args)
    {
        return static::is($args[0], $name);
    }
}