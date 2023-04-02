<?php

declare(strict_types=1);

namespace Larmias\Wind\Evaluator;

use Larmias\Wind\AST\ArrayLiteral;
use Larmias\Wind\AST\BlockStatement;
use Larmias\Wind\AST\Boolean;
use Larmias\Wind\AST\CallExpression;
use Larmias\Wind\AST\ExpressionInterface;
use Larmias\Wind\AST\ExpressionStatement;
use Larmias\Wind\AST\FunctionLiteral;
use Larmias\Wind\AST\HashLiteral;
use Larmias\Wind\AST\Identifier;
use Larmias\Wind\AST\IfExpression;
use Larmias\Wind\AST\IndexExpression;
use Larmias\Wind\AST\InfixExpression;
use Larmias\Wind\AST\IntegerLiteral;
use Larmias\Wind\AST\LetStatement;
use Larmias\Wind\AST\NodeInterface;
use Larmias\Wind\AST\PrefixExpression;
use Larmias\Wind\AST\Program;
use Larmias\Wind\AST\ReturnStatement;
use Larmias\Wind\AST\StringLiteral;
use Larmias\Wind\Environment\Environment;
use Larmias\Wind\Object\ArrayObject;
use Larmias\Wind\Object\Builtin;
use Larmias\Wind\Object\Error;
use Larmias\Wind\Object\FunctionObject;
use Larmias\Wind\Object\Hash;
use Larmias\Wind\Object\Hashable;
use Larmias\Wind\Object\HashParis;
use Larmias\Wind\Object\Integer;
use Larmias\Wind\Object\Nil;
use Larmias\Wind\Object\ObjectInterface;
use Larmias\Wind\Object\Boolean as BooleanObject;
use Larmias\Wind\Object\ObjectType;
use Larmias\Wind\Object\ReturnValue;
use Larmias\Wind\Object\StringObject;

class Evaluator
{
    public static array $cache = [];

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

        if ($node instanceof FunctionLiteral) {
            return new FunctionObject($node->parameters, $node->body, $env);
        }

        if ($node instanceof CallExpression) {
            $function = $this->eval($node->function, $env);
            if ($this->isError($function)) {
                return $function;
            }
            $args = $this->evalExpressions($node->arguments, $env);
            if (count($args) === 1 && $this->isError($args[0])) {
                return $args[0];
            }

            return $this->applyFunction($function, $args);
        }

        if ($node instanceof StringLiteral) {
            return new StringObject($node->value);
        }

        if ($node instanceof ArrayLiteral) {
            $elements = $this->evalExpressions($node->elements, $env);
            if (count($elements) === 1 && $this->isError($elements[0])) {
                return $elements[0];
            }
            return new ArrayObject($elements);
        }

        if ($node instanceof IndexExpression) {
            $left = $this->eval($node->left, $env);
            if ($this->isError($left)) {
                return $left;
            }
            $index = $this->eval($node->index, $env);
            if ($this->isError($index)) {
                return $index;
            }
            return $this->evalIndexExpression($left, $index);
        }

        if ($node instanceof HashLiteral) {
            return $this->evalHashLiteral($node, $env);
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
                return static::newError('unknown operator:%s%s', $operator, $right->getType()->getValue());
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

        if ($left->getType()->equals(ObjectType::STRING_OBJ) && $right->getType()->equals(ObjectType::STRING_OBJ)) {
            return $this->evalStringInfixExpression($operator, $left, $right);
        }

        if ($operator === '=') {
            return $this->nativeBoolToBooleanObject($left === $right);
        } else if ($operator === '!=') {
            return $this->nativeBoolToBooleanObject($left !== $right);
        } else if (!$left->getType()->equals($right)) {
            return static::newError('type mismatch: %s %s %s', $left->getType()->getValue(), $operator, $right->getType()->getValue());
        }

        return static::newError('unknown operator:%s %s %s', $left->getType()->getValue(), $operator, $right->getType()->getValue());
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

    protected function evalStringInfixExpression(string $operator, ObjectInterface $left, ObjectInterface $right): ?ObjectInterface
    {
        if ($operator !== '+') {
            return static::newError('unknown operator: %s %s %s', $left->getType()->getValue(), $operator, $right->getType()->getValue());
        }
        /** @var StringObject $left */
        /** @var StringObject $right */

        return new StringObject($left->value . $right->value);
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
        if ($value !== null) {
            return $value;
        }
        $builtin = Builtins::get($identifier->value);
        if ($builtin !== null) {
            return $builtin;
        }

        return static::newError('identifier not found: %s', $identifier->value);
    }

    /**
     * @param ExpressionInterface[] $exps
     * @param Environment $env
     * @return ObjectInterface[]
     */
    protected function evalExpressions(array $exps, Environment $env): array
    {
        $result = [];
        foreach ($exps as $exp) {
            $evaluated = $this->eval($exp, $env);
            if ($this->isError($evaluated)) {
                return [$evaluated];
            }
            $result[] = $evaluated;
        }
        return $result;
    }

    protected function evalIndexExpression(ObjectInterface $left, ObjectInterface $index): ?ObjectInterface
    {
        if ($left->getType()->equals(ObjectType::ARRAY_OBJ) && $index->getType()->equals(ObjectType::INTEGER_OBJ)) {
            return $this->evalArrayIndexExpression($left, $index);
        }

        if ($left->getType()->equals(ObjectType::HASH_OBJ)) {
            return $this->evalHashIndexExpression($left, $index);
        }

        return static::newError('index operator not supported: %s', $left->getType()->getValue());
    }

    protected function evalHashLiteral(HashLiteral $node, Environment $env): ObjectInterface
    {
        $paris = [];

        foreach ($node->pairs as $item) {
            [$keyNode, $valueNode] = [$item['key'], $item['value']];
            $key = $this->eval($keyNode, $env);
            if ($this->isError($key)) {
                return $key;
            }

            if (!($key instanceof Hashable)) {
                return static::newError('unusable as hash key: %s', $key->getType()->getValue());
            }

            $value = $this->eval($valueNode, $env);
            if ($this->isError($value)) {
                return $value;
            }
            $hashKey = $key->hashKey();
            $paris[$hashKey->value] = new HashParis($key, $value);
        }

        return new Hash($paris);
    }

    protected function evalArrayIndexExpression(ObjectInterface $array, ObjectInterface $index): ?ObjectInterface
    {
        /** @var ArrayObject $array */
        /** @var Integer $index */
        $max = count($array->elements) - 1;

        if ($index->value < 0 || $index->value > $max) {
            return static::$cache['nil'];
        }

        return $array->elements[$index->value];
    }

    protected function evalHashIndexExpression(ObjectInterface $hash, ObjectInterface $index): ?ObjectInterface
    {
        /** @var Hash $hash */
        /** @var Hashable $index */

        $hashPairs = $hash->pairs[$index->hashKey()->value] ?? null;
        if (!$hashPairs) {
            return static::$cache['nil'];
        }
        return $hashPairs->value;
    }

    /**
     * @param ObjectInterface $fn
     * @param ObjectInterface[] $args
     * @return ObjectInterface|null
     */
    protected function applyFunction(ObjectInterface $fn, array $args = []): ?ObjectInterface
    {
        if ($fn instanceof FunctionObject) {
            $extendedEnv = $this->extendFunctionEnv($fn, $args);
            $evaluated = $this->eval($fn->body, $extendedEnv);
            return $this->unwrapReturnValue($evaluated);
        }

        if ($fn instanceof Builtin) {
            return call_user_func($fn->fn, ...$args);
        }

        return static::newError('not a function: %s', $fn->getType()->getValue());
    }

    /**
     * @param FunctionObject $fn
     * @param ObjectInterface[] $args
     * @return Environment
     */
    protected function extendFunctionEnv(FunctionObject $fn, array $args = []): Environment
    {
        $env = Environment::new($fn->env);
        foreach ($fn->parameters as $index => $param) {
            $env->set($param->value, $args[$index]);
        }
        return $env;
    }

    protected function unwrapReturnValue(?ObjectInterface $object): ?ObjectInterface
    {
        if ($object instanceof ReturnValue) {
            return $object->value;
        }

        return $object;
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
    public static function newError(string $format, ...$args): Error
    {
        return new Error(sprintf($format, ...$args));
    }
}