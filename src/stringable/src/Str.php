<?php

declare(strict_types=1);

namespace Larmias\Stringable;

use Closure;
use Larmias\Macroable\Macroable;

class Str
{
    use Macroable;

    /**
     * @var int
     */
    public const CONVERT_SNAKE = 1;

    /**
     * @var int
     */
    public const CONVERT_CAMEL = 2;

    /**
     * @var int
     */
    public const CONVERT_STUDLY = 3;

    /**
     * @var array
     */
    protected static array $snakeCache = [];

    /**
     * @var array
     */
    protected static array $camelCache = [];

    /**
     * @var array
     */
    protected static array $studlyCache = [];

    /**
     * 检查字符串中是否包含某些字符串
     *
     * @param string $haystack
     * @param string|array $needles
     * @return bool
     */
    public static function contains(string $haystack, string|array $needles): bool
    {
        foreach ((array)$needles as $needle) {
            if ('' != $needle && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * 检查字符串是否以某些字符串结尾
     *
     * @param string $haystack
     * @param string|array $needles
     * @return bool
     */
    public static function endsWith(string $haystack, string|array $needles): bool
    {
        foreach ((array)$needles as $needle) {
            if ((string)$needle === static::substr($haystack, -static::length($needle))) {
                return true;
            }
        }

        return false;
    }

    /**
     * 检查字符串是否以某些字符串开头
     *
     * @param string $haystack
     * @param string|array $needles
     * @return bool
     */
    public static function startsWith(string $haystack, string|array $needles): bool
    {
        foreach ((array)$needles as $needle) {
            if ('' != $needle && mb_strpos($haystack, $needle) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * 获取指定长度的随机字母数字组合的字符串
     *
     * @param int $length
     * @param int|null $type
     * @param string $addChars
     * @return string
     */
    public static function random(int $length = 6, ?int $type = null, string $addChars = ''): string
    {
        $str = '';
        $chars = match ($type) {
            0 => 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' . $addChars,
            1 => str_repeat('0123456789', 3),
            2 => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $addChars,
            3 => 'abcdefghijklmnopqrstuvwxyz' . $addChars,
            4 => "们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书" . $addChars,
            default => 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789' . $addChars,
        };
        if ($length > 10) {
            $chars = $type == 1 ? str_repeat($chars, $length) : str_repeat($chars, 5);
        }
        if ($type != 4) {
            $chars = str_shuffle($chars);
            $str = substr($chars, 0, $length);
        } else {
            for ($i = 0; $i < $length; $i++) {
                $str .= mb_substr($chars, (int)floor(mt_rand(0, mb_strlen($chars, 'utf-8') - 1)), 1);
            }
        }
        return $str;
    }

    /**
     * 字符串转小写
     *
     * @param string $value
     * @return string
     */
    public static function lower(string $value): string
    {
        return mb_strtolower($value, 'UTF-8');
    }

    /**
     * 字符串转大写
     *
     * @param string $value
     * @return string
     */
    public static function upper(string $value): string
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    /**
     * 获取字符串的长度
     *
     * @param string $value
     * @return int
     */
    public static function length(string $value): int
    {
        return mb_strlen($value);
    }

    /**
     * 截取字符串
     *
     * @param string $string
     * @param int $start
     * @param int|null $length
     * @return string
     */
    public static function substr(string $string, int $start, int $length = null): string
    {
        return mb_substr($string, $start, $length, 'UTF-8');
    }

    /**
     * 驼峰转下划线
     *
     * @param string $value
     * @param string $delimiter
     * @return string
     */
    public static function snake(string $value, string $delimiter = '_'): string
    {
        $key = $value;

        if (isset(static::$snakeCache[$key][$delimiter])) {
            return static::$snakeCache[$key][$delimiter];
        }

        if (!ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));

            $value = static::lower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value));
        }

        return static::$snakeCache[$key][$delimiter] = $value;
    }

    /**
     * 下划线转驼峰(首字母小写)
     *
     * @param string $value
     * @return string
     */
    public static function camel(string $value): string
    {
        if (isset(static::$camelCache[$value])) {
            return static::$camelCache[$value];
        }

        return static::$camelCache[$value] = lcfirst(static::studly($value));
    }

    /**
     * 下划线转驼峰(首字母大写)
     *
     * @param string $value
     * @return string
     */
    public static function studly(string $value): string
    {
        $key = $value;

        if (isset(static::$studlyCache[$key])) {
            return static::$studlyCache[$key];
        }

        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return static::$studlyCache[$key] = str_replace(' ', '', $value);
    }

    /**
     * 名称转换
     *
     * @param string $value
     * @param int $type
     * @return string
     */
    public static function convertNameByType(string $value, int $type): string
    {
        return match ($type) {
            self::CONVERT_SNAKE => self::snake($value),
            self::CONVERT_CAMEL => self::camel($value),
            self::CONVERT_STUDLY => self::studly($value),
            default => $value,
        };
    }

    /**
     * 转为首字母大写的标题格式
     *
     * @param string $value
     * @return string
     */
    public static function title(string $value): string
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * 模板字符串解析
     *
     * @param string $content
     * @param array|Closure $vars
     * @param array|string[] $options
     * @return string
     */
    public static function template(string $content, array|Closure $vars = [], array $options = ['open' => '${', 'close' => '}']): string
    {
        return \preg_replace_callback(\sprintf(
            '/%s([\w]+)%s/', \preg_quote($options['open']), \preg_quote($options['close'])
        ), function ($match) use ($vars) {
            if ($vars instanceof Closure) {
                return $vars($match[1]);
            }
            return $vars[$match[1]] ?? '';
        }, $content);
    }

    /**
     * Determine if a given string matches a given pattern.
     *
     * @param string|array $pattern
     * @param string $value
     * @return bool
     */
    public static function is(string|array $pattern, string $value): bool
    {
        $patterns = (array)$pattern;

        if (empty($patterns)) {
            return false;
        }

        foreach ($patterns as $pattern) {
            $pattern = (string)$pattern;

            // If the given value is an exact match we can of course return true right
            // from the beginning. Otherwise, we will translate asterisks and do an
            // actual pattern match against the two strings to see if they match.
            if ($pattern === $value) {
                return true;
            }

            $pattern = \preg_quote($pattern, '#');

            // Asterisks are translated into zero-or-more regular expression wildcards
            // to make it convenient to check if the strings starts with the given
            // pattern such as "library/*", making any string check convenient.
            $pattern = \str_replace('\*', '.*', $pattern);

            if (\preg_match('#^' . $pattern . '\z#u', $value) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * 编码转换
     * @param string $content
     * @param string $encoding
     * @param string $deconding
     * @param array $encodes
     * @return string
     */
    public static function iconv(string $content, string $encoding = 'utf-8', string $deconding = '', array $encodes = ["ASCII", "UTF-8", "GBK", "BIG5", "Unicode"]): string
    {
        $encode = $deconding ?: \mb_detect_encoding($content, $encodes);
        return \iconv($encode, $encoding . '//IGNORE', $content);
    }
}
