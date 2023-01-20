<?php

declare(strict_types=1);

namespace Larmias\Rpn;

use RuntimeException;

class Lexer
{
	/**
	 * 当前读取的字符
	 * 
	 * @var string|null
	 */
	protected ?string $ch = null;

	/**
	 * 字符读取的当前位置
	 * 
	 * @var int
	 */
	protected int $position = 0;

	/**
	 * 字符读取的下一个位置
	 * 
	 * @var int
	 */
	protected int $readPosition = 0;

	/**
	 * 输入的字符串长度
	 * 
	 * @var int
	 */
	protected int $inputLen = 0;

	/**
	 * Lexer __construct.
	 * 
	 * @param string $input
	 * @param array $arguments
	 */
	public function __construct(protected string $input,protected array $arguments = [])
	{
		$this->inputLen = strlen($input);
	}

    /**
     * @param string $input
     * @param array $arguments
     * @return static
     */
	public static function new(string $input,array $arguments = []): static
    {
        return new static($input,$arguments);
    }

    /**
     * @return array
     */
	public function analyse(): array
    {
        $tokens = [];
        while ($token = $this->nextToken()) {
            $tokens[] = $token;
        }
        return $tokens;
    }

    /**
     * @return Token|null
     */
	public function nextToken(): ?Token
	{
	    $token = null;
        $this->readChar();
        $this->skipWhitespace();
	    $this->skipComment();

	    if (Utils::isOperator($this->ch)) {
            $token = new Token(Token::TYPE_OPERATOR,Token::TYPE_OPERATOR,$this->ch);
        } else if (Utils::isNumber($this->ch)) {
            $token = new Token(Token::TYPE_NUMBER,Token::TYPE_NUMBER,$this->makeNumber());
        } else if (Utils::isLetter($this->ch)) {
	        $identify = $this->makeIdentify();
	        if (!isset($this->arguments[$identify])) {
                $this->error(sprintf('%s argument value is null',$identify));
            }
	        $token = new Token(Token::TYPE_IDENTIFY,$identify,strval($this->arguments[$identify]));
        }
	    if (is_null($token) && $this->position != $this->inputLen) {
	        $this->error('unexpected character ' . $this->ch);
        }
	    return $token;
	}

    /**
     * @return string
     */
	protected function makeIdentify(): string
    {
        $identify = $this->ch;
        while (true) {
            if (!Utils::isLetter($this->peekChar())) {
                break;
            }
            $this->readChar();
            $identify .= $this->ch;
        }
        return $identify;
    }

    /**
     * @return string
     */
	protected function makeNumber(): string
    {
        $number = $this->ch;
        while (true) {
            $peekCh = $this->peekChar();
            if ($peekCh != '.' && !Utils::isNumber($peekCh)) {
                if (Utils::isLetter($peekCh)) {
                    $this->error('read number unexpected character ' . $peekCh . ' unknown ' . $number);
                }
                break;
            }
            $this->readChar();
            $number .= $this->ch;
        }
        return $number;
    }

    /**
     * @return void
     */
	protected function skipWhitespace(): void
    {
        while (ctype_space($this->ch)) {
            $this->readChar();
        }
    }

    /**
     * return void
     */
    protected function skipComment(): void
    {
        if ($this->ch == '/' && $this->peekChar() == '*') {
            while (true) {
                $this->readChar();
                if ($this->ch == '/') {
                    break;
                }
            }
            $this->readChar();
            $this->skipWhitespace();
        }
    }

	/**
	 * 读取一个字符
	 * 
	 * @return void
	 */
	protected function readChar(): void
	{
		if ($this->position >= $this->inputLen) {
			$this->ch = null;
		} else {
			$this->ch = $this->input[$this->position++];
			$this->readPosition = $this->position;
		}
	}

	/**
	 * 读取下一个字符
	 * 
	 * @return string|null
	 */
	protected function peekChar(): ?string
	{
		if ($this->readPosition >= $this->inputLen) {
			return null;
		}
		return $this->input[$this->readPosition];
	}

    /**
     * @param string $message
     * @throws RuntimeException
     */
	protected function error(string $message)
    {
        throw new RuntimeException($message . ' (position:' . $this->position . ')');
    }
}