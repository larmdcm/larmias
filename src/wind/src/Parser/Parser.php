<?php

declare (strict_types=1);

namespace Larmias\Wind\Parser;

use Larmias\Wind\AST\BlockStatement;
use Larmias\Wind\AST\Boolean;
use Larmias\Wind\AST\CallExpression;
use Larmias\Wind\AST\ExpressionInterface;
use Larmias\Wind\AST\ExpressionStatement;
use Larmias\Wind\AST\FunctionLiteral;
use Larmias\Wind\AST\Identifier;
use Larmias\Wind\AST\IfExpression;
use Larmias\Wind\AST\InfixExpression;
use Larmias\Wind\AST\IntegerLiteral;
use Larmias\Wind\AST\LetStatement;
use Larmias\Wind\AST\PrefixExpression;
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

    /**
     * @var PrefixParseFn[]
     */
    protected array $prefixParseFns = [];

    /**
     * @var InfixParseFn[]
     */
    protected array $infixParseFns = [];

    // 优先级
    public const LOWEST = 1;
    // ==
    public const EQUALS = 2;
    // > OR <
    public const LESSGREATER = 3;
    // +
    public const SUM = 4;
    // *
    public const PRODUCT = 5;
    // -X OR !X
    public const PREFIX = 6;
    // myFunction(X)
    public const CALL = 7;

    /**
     * 优先级表
     * @var array
     */
    protected array $precedences = [
        TokenType::EQ => self::EQUALS,
        TokenType::NOT_EQ => self::EQUALS,
        TokenType::LT => self::LESSGREATER,
        TokenType::GT => self::LESSGREATER,
        TokenType::PLUS => self::SUM,
        TokenType::MINUS => self::SUM,
        TokenType::SLASH => self::PRODUCT,
        TokenType::ASTERISK => self::PRODUCT,
        TokenType::LPAREN => self::CALL,
    ];

    public function __construct(protected Lexer $lexer)
    {
        $this->nextToken();
        $this->nextToken();

        $this->registerPrefix(TokenType::IDENT, new PrefixParseFn([$this, 'parseIdentifier']));
        $this->registerPrefix(TokenType::INT, new PrefixParseFn([$this, 'parseIntegerLiteral']));
        $this->registerPrefix(TokenType::BANG, new PrefixParseFn([$this, 'parsePrefixExpression']));
        $this->registerPrefix(TokenType::MINUS, new PrefixParseFn([$this, 'parsePrefixExpression']));
        $this->registerPrefix(TokenType::TRUE, new PrefixParseFn([$this, 'parseBoolean']));
        $this->registerPrefix(TokenType::FALSE, new PrefixParseFn([$this, 'parseBoolean']));
        $this->registerPrefix(TokenType::LPAREN, new PrefixParseFn([$this, 'parseGroupedExpression']));
        $this->registerPrefix(TokenType::IF, new PrefixParseFn([$this, 'parseIfExpression']));
        $this->registerPrefix(TokenType::FUNCTION, new PrefixParseFn([$this, 'parseFunctionLiteral']));

        $this->registerInfix(TokenType::PLUS, new InfixParseFn([$this, 'parseInfixExpression']));
        $this->registerInfix(TokenType::MINUS, new InfixParseFn([$this, 'parseInfixExpression']));
        $this->registerInfix(TokenType::SLASH, new InfixParseFn([$this, 'parseInfixExpression']));
        $this->registerInfix(TokenType::ASTERISK, new InfixParseFn([$this, 'parseInfixExpression']));
        $this->registerInfix(TokenType::EQ, new InfixParseFn([$this, 'parseInfixExpression']));
        $this->registerInfix(TokenType::NOT_EQ, new InfixParseFn([$this, 'parseInfixExpression']));
        $this->registerInfix(TokenType::LT, new InfixParseFn([$this, 'parseInfixExpression']));
        $this->registerInfix(TokenType::GT, new InfixParseFn([$this, 'parseInfixExpression']));
        $this->registerInfix(TokenType::LPAREN, new InfixParseFn([$this, 'parseCallExpression']));
    }

    public static function new(Lexer $lexer)
    {
        return new static($lexer);
    }

    /**
     * @param string $tokenType
     * @param PrefixParseFn $fn
     * @return void
     */
    public function registerPrefix(string $tokenType, PrefixParseFn $fn): void
    {
        $this->prefixParseFns[$tokenType] = $fn;
    }

    /**
     * @param string $tokenType
     * @param InfixParseFn $fn
     * @return void
     */
    public function registerInfix(string $tokenType, InfixParseFn $fn): void
    {
        $this->infixParseFns[$tokenType] = $fn;
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
            default:
                return $this->parseExpressionStatement();
        }
    }

    protected function parseExpressionStatement(): ?StatementInterface
    {
        $stmt = new ExpressionStatement($this->curToken, $this->parseExpression(self::LOWEST));
        if ($this->peekTokenIs(TokenType::SEMICOLON)) {
            $this->nextToken();
        }
        return $stmt;
    }

    protected function parseExpression(int $precedence): ?ExpressionInterface
    {
        $tokenType = $this->curToken->getTokenType()->getValue();
        $prefix = $this->prefixParseFns[$tokenType] ?? null;
        if (!$prefix) {
            throw new \RuntimeException(sprintf('no prefix parse function for %s found', $tokenType));
        }

        $leftExp = $prefix();
        while (!$this->peekTokenIs(TokenType::SEMICOLON) && $precedence < $this->peekPrecedence()) {
            $infix = $this->infixParseFns[$this->peekToken->getTokenType()->getValue()] ?? null;
            if (!$infix) {
                return $leftExp;
            }

            $this->nextToken();

            $leftExp = $infix($leftExp);
        }
        return $leftExp;
    }

    public function parseIdentifier(): ?ExpressionInterface
    {
        return new Identifier($this->curToken, $this->curToken->getLiteral());
    }

    public function parseIntegerLiteral(): ?ExpressionInterface
    {
        return new IntegerLiteral($this->curToken, intval($this->curToken->getLiteral()));
    }

    public function parsePrefixExpression(): ?ExpressionInterface
    {
        $expression = new PrefixExpression($this->curToken, $this->curToken->getLiteral());
        $this->nextToken();
        $expression->right = $this->parseExpression(self::PREFIX);

        return $expression;
    }

    public function parseBoolean(): ?ExpressionInterface
    {
        return new Boolean($this->curToken, $this->curTokenIs(TokenType::TRUE));
    }

    public function parseGroupedExpression(): ?ExpressionInterface
    {
        $this->nextToken();

        $exp = $this->parseExpression(self::LOWEST);

        if (!$this->expectPeek(TokenType::RPAREN)) {
            return null;
        }

        return $exp;
    }

    public function parseIfExpression(): ?ExpressionInterface
    {
        $expression = new IfExpression($this->curToken);

        if (!$this->expectPeek(TokenType::LPAREN)) {
            return null;
        }

        $this->nextToken();
        $expression->condition = $this->parseExpression(self::LOWEST);

        if (!$this->expectPeek(TokenType::RPAREN)) {
            return null;
        }

        if (!$this->expectPeek(TokenType::LBRACE)) {
            return null;
        }

        $expression->consequence = $this->parseBlockStatement();

        if ($this->peekTokenIs(TokenType::ELSE)) {
            $this->nextToken();

            if (!$this->expectPeek(TokenType::LBRACE)) {
                return null;
            }

            $expression->alternative = $this->parseBlockStatement();
        }

        return $expression;
    }

    public function parseBlockStatement(): BlockStatement
    {
        $block = new BlockStatement($this->curToken);

        $this->nextToken();

        while (!$this->curTokenIs(TokenType::RBRACE) && !$this->curTokenIs(TokenType::EOF)) {
            $stmt = $this->parseStatement();
            if ($stmt !== null) {
                $block->statements[] = $stmt;
            }
            $this->nextToken();
        }

        return $block;
    }

    public function parseFunctionLiteral(): ?ExpressionInterface
    {
        $lit = new FunctionLiteral($this->curToken);
        if (!$this->expectPeek(TokenType::LPAREN)) {
            return null;
        }
        $lit->parameters = $this->parseFunctionParameters();

        if (!$this->expectPeek(TokenType::LBRACE)) {
            return null;
        }

        $lit->body = $this->parseBlockStatement();

        return $lit;
    }

    public function parseFunctionParameters(): array
    {
        $identifiers = [];

        if ($this->peekTokenIs(TokenType::RPAREN)) {
            $this->nextToken();
            return $identifiers;
        }

        $this->nextToken();

        $identifiers[] = new Identifier($this->curToken, $this->curToken->getLiteral());

        while ($this->peekTokenIs(TokenType::COMMA)) {
            $this->nextToken();
            $this->nextToken();
            $identifiers[] = new Identifier($this->curToken, $this->curToken->getLiteral());
        }

        if (!$this->expectPeek(TokenType::RPAREN)) {
            return [];
        }

        return $identifiers;
    }

    public function parseInfixExpression(?ExpressionInterface $left): ?ExpressionInterface
    {
        $expression = new InfixExpression($this->curToken, $left, $this->curToken->getLiteral());
        $precedence = $this->curPrecedence();
        $this->nextToken();
        $expression->right = $this->parseExpression($precedence);
        return $expression;
    }

    public function parseCallExpression(?ExpressionInterface $function): ?ExpressionInterface
    {
        return new CallExpression($this->curToken, $function, $this->parseCallArguments());
    }

    public function parseCallArguments(): array
    {
        $args = [];

        if ($this->peekTokenIs(TokenType::RPAREN)) {
            $this->nextToken();
            return $args;
        }

        $this->nextToken();
        $args[] = $this->parseExpression(self::LOWEST);

        while ($this->peekTokenIs(TokenType::COMMA)) {
            $this->nextToken();
            $this->nextToken();
            $args[] = $this->parseExpression(self::LOWEST);
        }

        if (!$this->expectPeek(TokenType::RPAREN)) {
            return [];
        }

        return $args;
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

        $this->nextToken();

        $stmt->value = $this->parseExpression(self::LOWEST);

        if ($this->peekTokenIs(TokenType::SEMICOLON)) {
            $this->nextToken();
        }

        return $stmt;
    }

    protected function parseReturnStatement(): ?ReturnStatement
    {
        $stmt = new ReturnStatement($this->curToken);
        $this->nextToken();

        $stmt->returnValue = $this->parseExpression(self::LOWEST);

        if (!$this->peekTokenIs(TokenType::SEMICOLON)) {
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

    protected function peekPrecedence(): int
    {
        return $this->precedences[$this->peekToken->getTokenType()->getValue()] ?? self::LOWEST;
    }

    protected function curPrecedence(): int
    {
        return $this->precedences[$this->curToken->getTokenType()->getValue()] ?? self::LOWEST;
    }

    protected function nextToken(): void
    {
        $this->curToken = $this->peekToken;
        $this->peekToken = $this->lexer->nextToken();
    }
}