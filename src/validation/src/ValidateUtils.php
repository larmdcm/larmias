<?php

declare(strict_types=1);

namespace Larmias\Validation;

use InvalidArgumentException;
use Larmias\Validation\Exceptions\RuleException;
use Larmias\Stringable\Str;
use RuntimeException;
use SplFileInfo;
use Throwable;
use function is_numeric;
use function in_array;
use function is_array;
use function checkdnsrr;
use function Larmias\Support\is_empty;
use function strtotime;
use function ctype_digit;
use function ctype_alnum;
use function is_string;
use function strpos;
use function function_exists;
use function explode;
use function filter_var;
use function is_int;
use function filter_id;
use function str_starts_with;
use function is_scalar;
use function preg_match;
use function count;
use function mb_strlen;
use const FILTER_VALIDATE_EMAIL;
use const FILTER_VALIDATE_IP;
use const FILTER_FLAG_IPV4;
use const FILTER_FLAG_IPV6;
use const FILTER_VALIDATE_INT;
use const FILTER_VALIDATE_URL;
use const FILTER_VALIDATE_MAC;
use const FILTER_VALIDATE_FLOAT;

/**
 * @method static bool isRequired(mixed $value)
 * @method static bool isAccepted(mixed $value)
 * @method static bool isDate(mixed $value)
 * @method static bool isActiveUrl(mixed $value)
 * @method static bool isBoolean(mixed $value)
 * @method static bool isNumber(mixed $value)
 * @method static bool isAlphaNum(mixed $value)
 * @method static bool isArray(mixed $value)
 * @method static bool isMobile(mixed $value)
 * @method static bool isIdCard(mixed $value)
 * @method static bool isZip(mixed $value)
 * @method static bool isEmail(mixed $value)
 * @method static bool isInteger(mixed $value)
 * @method static bool isUrl(mixed $value)
 * @method static bool isMacAddr(mixed $value)
 * @method static bool isFloat(mixed $value)
 * @method static bool isFile(mixed $value)
 * @method static bool isImage(mixed $value)
 */
class ValidateUtils
{
    /**
     * 内置正则表达式
     * @var array|string[]
     */
    protected static array $regex = [
        'mobile' => '/^1[3-9]\d{9}$/',
        'idCard' => '/(^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$)|(^[1-9]\d{5}\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}$)/',
        'zip' => '/\d{6}/',
    ];

    /**
     * 内置filter规则
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
     * @param mixed $value
     * @param string $rule
     * @return bool
     */
    public static function is(mixed $value, string $rule): bool
    {
        switch (Str::camel($rule)) {
            case 'required':
                // 必须
                $result = !is_empty($value);
                break;
            case 'accepted':
                // 接受
                $result = in_array($value, ['1', 'on', 'yes']);
                break;
            case 'date':
                // 是否是一个有效日期
                $result = false !== strtotime($value);
                break;
            case 'activeUrl':
                // 是否为有效的网址
                $result = checkdnsrr($value);
                break;
            case 'boolean':
            case 'bool':
                // 是否为布尔值
                $result = in_array($value, [true, false, 0, 1, '0', '1'], true);
                break;
            case 'number':
                $result = ctype_digit((string)$value);
                break;
            case 'alphaNum':
                $result = ctype_alnum($value);
                break;
            case 'array':
                // 是否为数组
                $result = is_array($value);
                break;
            case 'file':
                $result = $value instanceof SplFileInfo;
                break;
            case 'image':
                $result = $value instanceof SplFileInfo && in_array(static::getImageType($value->getPath() . DIRECTORY_SEPARATOR . $value->getFilename()), [1, 2, 3, 6]);
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
     * @param int|string|array $rule
     * @return bool
     */
    public static function filter(mixed $value, int|string|array $rule): bool
    {
        if (is_string($rule) && strpos($rule, ',')) {
            [$rule, $param] = explode(',', $rule);
        } elseif (is_array($rule)) {
            $param = $rule[1] ?? 0;
            $rule = $rule[0];
        } else {
            $param = 0;
        }
        return false !== filter_var($value, is_int($rule) ? $rule : filter_id($rule), $param);
    }

    /**
     * @param mixed $value
     * @param string $rule
     * @return bool
     */
    public static function regex(mixed $value, string $rule): bool
    {
        if (!str_starts_with($rule, '/') && !preg_match('/\/[imsU]{0,4}$/', $rule)) {
            // 不是正则表达式则两端补上/
            $rule = '/^' . $rule . '$/';
        }

        return is_scalar($value) && 1 === preg_match($rule, (string)$value);
    }

    /**
     * @param mixed $value
     * @param mixed $maxValue
     * @return bool
     */
    public static function max(mixed $value, mixed $maxValue): bool
    {
        return $value <= $maxValue;
    }

    /**
     * @param mixed $value
     * @param mixed $minValue
     * @return bool
     */
    public static function min(mixed $value, mixed $minValue): bool
    {
        return $value >= $minValue;
    }

    /**
     * @param mixed $value
     * @param ...$args
     * @return bool
     */
    public static function in(mixed $value, ...$args): bool
    {
        $values = isset($args[0]) && is_array($args[0]) ? $args[0] : $args;
        return in_array($value, $values);
    }

    /**
     * @param mixed $value
     * @param ...$args
     * @return bool
     */
    public static function notIn(mixed $value, ...$args): bool
    {
        return !static::in($value, ...$args);
    }

    /**
     * @param mixed $value
     * @param mixed $min
     * @param mixed $max
     * @return bool
     */
    public static function between(mixed $value, mixed $min, mixed $max): bool
    {
        return $value >= $min && $value <= $max;
    }

    /**
     * @param mixed $value
     * @param mixed $min
     * @param mixed $max
     * @return bool
     */
    public static function notBetween(mixed $value, mixed $min, mixed $max): bool
    {
        return !static::between($value, $min, $max);
    }

    /**
     * @param mixed $value
     * @param mixed $length
     * @return bool
     */
    public static function length(mixed $value, mixed $length): bool
    {
        return $value === $length;
    }

    /**
     * 验证时间和日期是否符合指定格式
     * @param string $value 字段值
     * @param string $rule 验证规则
     * @return bool
     */
    public static function dateFormat(string $value, string $rule): bool
    {
        $info = date_parse_from_format($rule, $value);
        return 0 == $info['warning_count'] && 0 == $info['error_count'];
    }

    /**
     * 检测上传文件类型
     * @param SplFileInfo|string $file
     * @param array|string $mime
     * @return bool
     */
    public static function checkFileMime(SplFileInfo|string $file, array|string $mime): bool
    {
        if (is_string($mime)) {
            $mime = explode(',', $mime);
        }

        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $filePath = $file instanceof SplFileInfo ? $file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename() : $file;

        return in_array(strtolower(finfo_file($fileInfo, $filePath)), $mime, true);
    }

    /**
     * 检测上传文件后缀
     * @param SplFileInfo|string $file
     * @param array|string $ext
     * @return bool
     */
    public static function checkFileExt(SplFileInfo|string $file, array|string $ext): bool
    {
        if (is_string($ext)) {
            $ext = explode(',', $ext);
        }
        
        if ($file instanceof SplFileInfo) {
            $fileExt = $file->getExtension();
        } else {
            $split = explode('.', $file);
            $fileExt = end($split);
        }

        return in_array(strtolower($fileExt), $ext, true);
    }

    /**
     * 获取数组|文件|字符串|数值 -> 大小或长度
     * @param mixed $value
     * @param bool $numeric
     * @return int|float
     */
    public static function getSize(mixed $value, bool $numeric = false): int|float
    {
        if (is_numeric($value) && $numeric) {
            return $value;
        }

        if (is_array($value)) {
            return count($value);
        }

        if ($value instanceof SplFileInfo) {
            return $value->getSize() / 1024;
        }

        return mb_strlen((string)$value);
    }

    /**
     * 值比较验证
     * @param mixed $first
     * @param mixed $second
     * @param string $operator
     * @return bool
     */
    public static function compare(mixed $first, mixed $second, string $operator = '='): bool
    {
        return match ($operator) {
            '<' => $first < $second,
            '>' => $first > $second,
            '<=' => $first <= $second,
            '>=' => $first >= $second,
            '=' => $first == $second,
            default => throw new InvalidArgumentException(),
        };
    }


    /**
     * 获取图片类型
     * @param string $image
     * @return false|int
     */
    public static function getImageType(string $image): int|false
    {
        if (function_exists('exif_imagetype')) {
            return exif_imagetype($image);
        }

        try {
            $info = getimagesize($image);
            return $info ? $info[2] : false;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @param string $name
     * @param array $args
     * @return bool
     */
    public static function __callStatic(string $name, array $args)
    {
        if (str_starts_with($name, 'is')) {
            return static::is($args[0], lcfirst(substr($name, 2)));
        }

        throw new RuntimeException(__CLASS__ . '::' . $name . ' is not a function.');
    }
}