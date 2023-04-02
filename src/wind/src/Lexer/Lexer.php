<?php

declare (strict_types=1);

namespace Larmias\Wind\Lexer;

class Lexer
{
    /**
     * @var int
     */
    protected int $position = 0;

    /**
     * @var int
     */
    protected int $readPosition = 0;

    /**
     * @var string
     */
    protected string $ch = '';

    /**
     * @param string $input
     */
    public function __construct(protected string $input)
    {
        $this->readChar();
    }

    /**
     * @param string $input
     * @return static
     */
    public static function new(string $input): static
    {
        return new static($input);
    }

    /**
     * @return Token
     */
    public function nextToken(): Token
    {
        $this->skipWhitespace();

        switch ($this->ch) {
            case '=':
                if ($this->peekChar() === '=') {
                    $ch = $this->ch;
                    $this->readChar();
                    $token = Token::new(TokenType::EQ, $ch . $this->ch);
                } else {
                    $token = Token::new(TokenType::ASSIGN, $this->ch);
                }
                break;
            case '+':
                $token = Token::new(TokenType::PLUS, $this->ch);
                break;
            case '-':
                $token = Token::new(TokenType::MINUS, $this->ch);
                break;
            case '!':
                if ($this->peekChar() === '=') {
                    $ch = $this->ch;
                    $this->readChar();
                    $token = Token::new(TokenType::NOT_EQ, $ch . $this->ch);
                } else {
                    $token = Token::new(TokenType::BANG, $this->ch);
                }
                break;
            case '/':
                $token = Token::new(TokenType::SLASH, $this->ch);
                break;
            case '*':
                $token = Token::new(TokenType::ASTERISK, $this->ch);
                break;
            case '<':
                $token = Token::new(TokenType::LT, $this->ch);
                break;
            case '>':
                $token = Token::new(TokenType::GT, $this->ch);
                break;
            case ';':
                $token = Token::new(TokenType::SEMICOLON, $this->ch);
                break;
            case ',':
                $token = Token::new(TokenType::COMMA, $this->ch);
                break;
            case '(':
                $token = Token::new(TokenType::LPAREN, $this->ch);
                break;
            case ')':
                $token = Token::new(TokenType::RPAREN, $this->ch);
                break;
            case '{':
                $token = Token::new(TokenType::LBRACE, $this->ch);
                break;
            case '}':
                $token = Token::new(TokenType::RBRACE, $this->ch);
                break;
            case "\0":
                $token = Token::new(TokenType::EOF);
                break;
            case '"':
                $token = Token::new(TokenType::STRING, $this->readString());
                break;
            case '[':
                $token = Token::new(TokenType::LBRACKET, $this->ch);
                break;
            case ']':
                $token = Token::new(TokenType::RBRACKET, $this->ch);
                break;
            case ':':
                $token = Token::new(TokenType::COLON, $this->ch);
                break;
            default:
                if ($this->isLetter($this->ch)) {
                    $ident = $this->readIdentifier();
                    return Token::new(Token::lookupIdent($ident), $ident);
                } else if ($this->isDigit($this->ch)) {
                    return Token::new(TokenType::INT, $this->readNumber());
                }
                $token = Token::new(TokenType::ILLEGAL, $this->ch);
        }

        $this->readChar();

        return $token;
    }

    /**
     * 跳过空白字符
     * @return void
     */
    public function skipWhitespace(): void
    {
        while ($this->ch === ' ' || $this->ch === "\t" || $this->ch === "\n" || $this->ch === "\r") {
            $this->readChar();
        }
    }

    /**
     * 读取一个字符
     * @return void
     */
    public function readChar(): void
    {
        if ($this->readPosition >= strlen($this->input)) {
            $this->ch = "\0";
        } else {
            $this->ch = $this->input[$this->readPosition];
        }
        $this->position = $this->readPosition;
        $this->readPosition++;
    }

    /**
     * 查看下一个字符
     * @return string
     */
    public function peekChar(): string
    {
        if ($this->readPosition >= strlen($this->input)) {
            return '0';
        }

        return $this->input[$this->readPosition];
    }

    /**
     * 读取一个标识符
     * @return string
     */
    public function readIdentifier(): string
    {
        $position = $this->position;
        while ($this->isLetter($this->ch)) {
            $this->readChar();
        }

        return substr($this->input, $position, $this->position - $position);
    }

    /**
     * 读取一个数值
     * @return string
     */
    public function readNumber(): string
    {
        $position = $this->position;
        while ($this->isDigit($this->ch)) {
            $this->readChar();
        }

        return substr($this->input, $position, $this->position - $position);
    }

    /**
     * @return string
     */
    protected function readString(): string
    {
        $position = $this->position + 1;
        while (true) {
            $this->readChar();
            if ($this->ch === '"' || $this->ch === "\0") {
                break;
            }
        }

        return substr($this->input, $position, $this->position - $position);
    }

    /**
     * @param string $value
     * @return bool
     */
    public function isDigit(string $value): bool
    {
        return ctype_digit($value);
    }

    /**
     * 是否是一个字面量
     * @param string $ch
     * @return bool
     */
    public function isLetter(string $ch): bool
    {
        return ctype_alpha($ch);
    }
}