<?php

declare (strict_types=1);

namespace Larmias\Wind\Lexer;

class Token
{
    protected static array $keywords = [
        'let' => TokenType::LET,
        'fn' => TokenType::FUNCTION,
        'true' => TokenType::TRUE,
        'false' => TokenType::FALSE,
        'if' => TokenType::IF,
        'else' => TokenType::ELSE,
        'return' => TokenType::RETURN,
    ];

    /**
     * @param TokenType $tokenType 类型
     * @param string $literal 字面量
     */
    public function __construct(protected TokenType $tokenType, protected string $literal)
    {
    }

    /**
     * @param TokenType|string $tokenType
     * @param string $literal
     * @return static
     */
    public static function new(TokenType|string $tokenType, string $literal = ''): static
    {
        return new static($tokenType instanceof TokenType ? $tokenType : TokenType::new($tokenType), $literal);
    }

    public static function lookupIdent(string $ident): TokenType
    {
        if (isset(static::$keywords[$ident])) {
            return TokenType::new(static::$keywords[$ident]);
        }

        return TokenType::new(TokenType::IDENT);
    }

    /**
     * @return TokenType
     */
    public function getTokenType(): TokenType
    {
        return $this->tokenType;
    }

    /**
     * @param TokenType $tokenType
     */
    public function setTokenType(TokenType $tokenType): void
    {
        $this->tokenType = $tokenType;
    }

    /**
     * @return string
     */
    public function getLiteral(): string
    {
        return $this->literal;
    }

    /**
     * @param string $literal
     */
    public function setLiteral(string $literal): void
    {
        $this->literal = $literal;
    }
}