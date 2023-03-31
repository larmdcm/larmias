<?php

declare (strict_types=1);

namespace Larmias\Wind\Lexer;

class TokenType
{
    /**
     * 非法字符
     * @var string
     */
    public const ILLEGAL = 'ILLEGAL';

    /**
     * 结束字符
     * @var string
     */
    public const EOF = 'EOF';

    /**
     * 标识符
     * @var string
     */
    public const IDENT = 'IDENT';

    /**
     * 字面量
     * @var string
     */
    public const INT = 'INT';

    /**
     * 赋值符
     * @var string
     */
    public const ASSIGN = '=';

    /**
     * 加号
     * @var string
     */
    public const PLUS = '+';

    /**
     * 减号
     * @var string
     */
    public const MINUS = '-';

    /**
     * 感叹号
     * @var string
     */
    public const BANG = '!';

    /**
     * 乘号
     * @var string
     */
    public const ASTERISK = '*';

    /**
     * 除号
     * @var string
     */
    public const SLASH = '/';

    /**
     * 小于
     * @var string
     */
    public const LT = '<';

    /**
     * 大于
     * @var string
     */
    public const GT = '>';

    /**
     * 等于
     * @var string
     */
    public const EQ = '==';

    /**
     * 不等于
     * @var string
     */
    public const NOT_EQ = '!=';

    /**
     * 分隔符逗号
     * @var string
     */
    public const COMMA = ',';

    /**
     * 分隔符分号
     * @var string
     */
    public const SEMICOLON = ';';

    /**
     * 左括号
     * @var string
     */
    public const LPAREN = '(';

    /**
     * 右括号
     * @var string
     */
    public const RPAREN = ')';

    /**
     * 左大括号
     * @var string
     */
    public const LBRACE = '{';

    /**
     * 右大括号
     * @var string
     */
    public const RBRACE = '}';

    /**
     * 关键词函数
     * @var string
     */
    public const FUNCTION = 'FUNCTION';

    /**
     * 关键词变量定义
     * @var string
     */
    public const LET = 'LET';

    /**
     * 关键词
     * @var string
     */
    public const TRUE = 'TRUE';

    /**
     * 关键词
     * @var string
     */
    public const FALSE = 'FALSE';

    /**
     * 关键词
     * @var string
     */
    public const IF = 'IF';

    /**
     * 关键词
     * @var string
     */
    public const ELSE = 'ELSE';

    /**
     * 关键词
     * @var string
     */
    public const RETURN = 'RETURN';

    /**
     * @var array
     */
    protected static array $cache = [];


    /**
     * @param string $name
     * @param string $value
     */
    public function __construct(protected string $name, protected string $value)
    {
    }

    /**
     * @param string $type
     * @return static
     */
    public static function new(string $type): static
    {
        if (empty(static::$cache)) {
            $reflect = new \ReflectionClass(static::class);
            static::$cache = $reflect->getConstants();
            static::$cache = array_combine(array_values(static::$cache), array_keys(static::$cache));
        }
        return new static(static::$cache[$type], $type);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}