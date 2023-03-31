<?php

declare (strict_types=1);

namespace Larmias\Wind\Parser;

use Larmias\Wind\AST\Identifier;
use Larmias\Wind\AST\LetStatement;
use Larmias\Wind\AST\Program;
use Larmias\Wind\AST\ReturnStatement;
use Larmias\Wind\AST\StatementInterface;
use Larmias\Wind\Lexer\Lexer;
use Larmias\Wind\Lexer\Token;
use Larmias\Wind\Lexer\TokenType;

class Parser
{
    protected ?Token $curToken = null;

    protected ?Token $peekToken = null;

    public function __construct(protected Lexer $lexer)
    {
        $this->nextToken();
        $this->nextToken();
    }

    public static function new(Lexer $lexer)
    {
        return new static($lexer);
    }


    public function parseProgram(): ?Program
    {
        $program = new Program();


        while ($this->curToken->getTokenType()->getValue() !== TokenType::EOF) {
            $stmt = $this->parseStatement();
            if ($stmt !== null) {
                $program->statements[] = $stmt;
            }
            $this->nextToken();
        }

        return $program;
    }

    protected function parseStatement(): ?StatementInterface
    {
        switch ($this->curToken->getTokenType()->getValue()) {
            case TokenType::LET:
                return $this->parseLetStatement();
            case TokenType::RETURN:
                return $this->parseReturnStatement();
        }
        return null;
    }

    protected function parseLetStatement(): ?LetStatement
    {
        $stmt = new LetStatement();
        $stmt->token = $this->curToken;

        if (!$this->expectPeek(TokenType::IDENT)) {
            return null;
        }

        $stmt->name = new Identifier($this->curToken, $this->curToken->getLiteral());

        if (!$this->expectPeek(TokenType::ASSIGN)) {
            return null;
        }

        // TODO
        if (!$this->curTokenIs(TokenType::SEMICOLON)) {
            $this->nextToken();
        }

        return $stmt;
    }

    protected function parseReturnStatement(): ?ReturnStatement
    {
        $stmt = new ReturnStatement($this->curToken);
        $this->nextToken();

        // TODO
        if (!$this->curTokenIs(TokenType::SEMICOLON)) {
            $this->nextToken();
        }

        return $stmt;
    }

    /**
     * @param string $tokenType
     * @return bool
     */
    protected function curTokenIs(string $tokenType): bool
    {
        return $this->curToken->getTokenType()->getValue() === $tokenType;
    }

    /**
     * @param string $tokenType
     * @return bool
     */
    protected function peekTokenIs(string $tokenType): bool
    {
        return $this->peekToken->getTokenType()->getValue() === $tokenType;
    }

    /**
     * @param string $tokenType
     * @return bool
     */
    protected function expectPeek(string $tokenType): bool
    {
        if ($this->peekTokenIs($tokenType)) {
            $this->nextToken();
            return true;
        }

        throw new \RuntimeException(sprintf('expected next token to be %s,got %s instead',
            $tokenType, $this->peekToken->getTokenType()->getValue()));
    }

    protected function nextToken(): void
    {
        $this->curToken = $this->peekToken;
        $this->peekToken = $this->lexer->nextToken();
    }
}