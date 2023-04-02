<?php

declare(strict_types=1);

namespace Larmias\Wind\Evaluator;

use Larmias\Wind\AST\BlockStatement;
use Larmias\Wind\AST\Boolean;
use Larmias\Wind\AST\ExpressionStatement;
use Larmias\Wind\AST\Identifier;
use Larmias\Wind\AST\IfExpression;
use Larmias\Wind\AST\InfixExpression;
use Larmias\Wind\AST\IntegerLiteral;
use Larmias\Wind\AST\LetStatement;
use Larmias\Wind\AST\NodeInterface;
use Larmias\Wind\AST\PrefixExpression;
use Larmias\Wind\AST\Program;
use Larmias\Wind\AST\ReturnStatement;
use Larmias\Wind\Environment\Environment;
use Larmias\Wind\Object\Error;
use Larmias\Wind\Object\Integer;
use Larmias\Wind\Object\Nil;
use Larmias\Wind\Object\ObjectInterface;
use Larmias\Wind\Object\Boolean as BooleanObject;
use Larmias\Wind\Object\ObjectType;
use Larmias\Wind\Object\ReturnValue;

class Evaluator
{
    protected static array $cache = [];

    public function __construct()
    {
        if (empty(static::$cache)) {
            static::$cache['true'] = new BooleanObject(true);
            static::$cache['false'] = new BooleanObject(false);
            static::$cache['nil'] = new Nil();
        }
    }

    public function eval(NodeInterface $node, Environment $env): ?ObjectInterface
    {
        // 语句
        if ($node instanceof Program) {
            return $this->evalProgram($node->statements, $env);
        }

        if ($node instanceof ExpressionStatement) {
            return $this->eval($node->expression, $env);
        }

        if ($node instanceof BlockStatement) {
            return $this->evalBlockStatement($node, $env);
        }

        if ($node instanceof IfExpression) {
            return $this->evalIfExpression($node, $env);
        }

        if ($node instanceof ReturnStatement) {
            $value = $this->eval($node->returnValue, $env);
            if ($this->isError($value)) {
                return $value;
            }
            return new ReturnValue($value);
        }

        if ($node instanceof LetStatement) {
            $value = $this->eval($node->value, $env);
            if ($this->isError($value)) {
                return $value;
            }
            $env->set($node->name->value, $value);
        }

        if ($node instanceof Identifier) {
            return $this->evalIdentifier($node, $env);
        }


        // 表达式
        if ($node instanceof IntegerLiteral) {
            return new Integer($node->value);
        }

        if ($node instanceof Boolean) {
            return $node->value ? static::$cache['true'] : static::$cache['false'];
        }

        if ($node instanceof PrefixExpression) {
            $right = $this->eval($node->right, $env);
            if ($this->isError($right)) {
                return $right;
            }
            return $this->evalPrefixExpression($node->operator, $right);
        }

        if ($node instanceof InfixExpression) {
            $left = $this->eval($node->left, $env);
            if ($this->isError($left)) {
                return $left;
            }
            $right = $this->eval($node->right, $env);
            if ($this->isError($right)) {
                return $right;
            }
            return $this->evalInfixExpression($node->operator, $left, $right);
        }

        return null;
    }

    protected function evalPrefixExpression(string $operator, ObjectInterface $right): ?ObjectInterface
    {
        switch ($operator) {
            case '!':
                return $this->evalBangOperatorExpression($right);
            case '-':
                return $this->evalMinusPrefixOperatorExpression($right);
            default:
                return $this->newError('unknown operator:%s%s', $operator, $right->getType()->getValue());
        }
    }

    protected function evalMinusPrefixOperatorExpression(ObjectInterface $right): ?ObjectInterface
    {
        if (!$right->getType()->equals(ObjectType::INTEGER_OBJ)) {
            return static::$cache['nil'];
        }
        /** @var Integer $right */
        return new Integer(-$right->value);
    }

    protected function evalBangOperatorExpression(ObjectInterface $right): ?ObjectInterface
    {
        if ($right === static::$cache['true']) {
            return static::$cache['false'];
        }

        if ($right === static::$cache['false']) {
            return static::$cache['true'];
        }

        if ($right === static::$cache['nil']) {
            return static::$cache['true'];
        }

        return static::$cache['false'];
    }

    protected function evalInfixExpression(string $operator, ObjectInterface $left, ObjectInterface $right): ?ObjectInterface
    {
        if ($left->getType()->equals(ObjectType::INTEGER_OBJ) && $right->getType()->equals(ObjectType::INTEGER_OBJ)) {
            return $this->evalIntegerInfixExpression($operator, $left, $right);
        }

        if ($operator === '=') {
            return $this->nativeBoolToBooleanObject($left === $right);
        } else if ($operator === '!=') {
            return $this->nativeBoolToBooleanObject($left !== $right);
        } else if (!$left->getType()->equals($right)) {
            return $this->newError('type mismatch: %s %s %s', $left->getType()->getValue(), $operator, $right->getType()->getValue());
        }

        return $this->newError('unknown operator:%s %s %s', $left->getType()->getValue(), $operator, $right->getType()->getValue());
    }

    protected function evalIntegerInfixExpression(string $operator, ObjectInterface $left, ObjectInterface $right): ?ObjectInterface
    {
        /** @var Integer $left */
        /** @var Integer $right */
        return match ($operator) {
            '+' => new Integer($left->value + $right->value),
            '-' => new Integer($left->value - $right->value),
            '*' => new Integer($left->value * $right->value),
            '/' => new Integer($left->value / $right->value),
            '<' => $this->nativeBoolToBooleanObject($left->value < $right->value),
            '>' => $this->nativeBoolToBooleanObject($left->value > $right->value),
            '==' => $this->nativeBoolToBooleanObject($left->value == $right->value),
            '!=' => $this->nativeBoolToBooleanObject($left->value != $right->value),
            default => static::$cache['nil']
        };
    }

    protected function nativeBoolToBooleanObject(bool $condition): BooleanObject
    {
        return $condition ? static::$cache['true'] : static::$cache['false'];
    }

    /**
     * @param array $statements
     * @param Environment $env
     * @return ObjectInterface|null
     */
    protected function evalProgram(array $statements, Environment $env): ?ObjectInterface
    {
        $result = null;

        foreach ($statements as $statement) {
            $result = $this->eval($statement, $env);
            if ($result instanceof ReturnValue) {
                return $result;
            } else if ($result instanceof Error) {
                return $result;
            }
        }

        return $result;
    }

    protected function evalBlockStatement(BlockStatement $block, Environment $env): ?ObjectInterface
    {
        $result = null;

        foreach ($block->statements as $statement) {
            $result = $this->eval($statement, $env);
            if ($result !== null &&
                ($result->getType()->equals(ObjectType::RETURN_VALUE_OBJ) || $result->getType()->equals(ObjectType::ERROR_OBJ))) {
                return $result;
            }
        }

        return $result;
    }

    protected function evalIfExpression(IfExpression $expression, Environment $env): ?ObjectInterface
    {
        $condition = $this->eval($expression->condition, $env);
        if ($this->isError($condition)) {
            return $condition;
        }

        if ($this->isTruthy($condition)) {
            return $this->eval($expression->consequence, $env);
        } else if ($expression->alternative !== null) {
            return $this->eval($expression->alternative, $env);
        }

        return static::$cache['nil'];
    }

    protected function evalIdentifier(Identifier $identifier, Environment $env): ObjectInterface
    {
        $value = $env->get($identifier->value);
        if ($value === null) {
            return $this->newError('identifier not found: %s', $identifier->value);
        }

        return $value;
    }

    protected function isTruthy(ObjectInterface $object): bool
    {
        if ($object === static::$cache['nil']) {
            return false;
        }

        if ($object === static::$cache['true']) {
            return true;
        }

        if ($object === static::$cache['false']) {
            return false;
        }

        return true;
    }

    /**
     * @param ObjectInterface|null $object
     * @return bool
     */
    protected function isError(?ObjectInterface $object): bool
    {
        return $object !== null && $object->getType()->equals(ObjectType::ERROR_OBJ);
    }

    /**
     * @param string $format
     * @param ...$args
     * @return Error
     */
    protected function newError(string $format, ...$args): Error
    {
        return new Error(sprintf($format, ...$args));
    }
}