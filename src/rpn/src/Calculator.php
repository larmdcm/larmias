<?php

declare(strict_types=1);

namespace Larmias\Rpn;

use SplQueue;
use SplStack;
use RuntimeException;

class Calculator
{
    /**
     * @var array
     */
    protected array $operators = [];

    /**
     * @var array|int[]
     */
    protected array $priority = ['(' => 10, ')' => 10, '+' => 20, '-' => 20, '*' => 30, '/' => 30];

    /**
     * Calculator constructor.
     *
     * @param array $operators
     */
    public function __construct(array $operators = [])
    {
        $this->operators = array_merge($this->getDefaultOperators(),$operators);
    }

    /**
     * @param string $expression
     * @param array $arguments
     * @param int $scale
     */
    public function calculate(string $expression,array $arguments = [],int $scale = 0)
    {
        $queue  = new SplQueue();
        $tokens = $this->analyseRpnExpression($expression,$arguments);
        /** @var Token $token */
        foreach ($tokens as $token) {
            if (!$token->isOperator()) {
                $queue->push($token);
                continue;
            }
            $params = [];
            for ($i = 0; $i < 2; $i++) {
                /** @var Token $value */
                $value = $queue->pop();
                if (!$value->isNumber()) {
                    throw new RuntimeException(sprintf('The value %s is invalid.', $value));
                }
                $params[] = $value->getValue();
            }
            $queue->push(new Token(Token::TYPE_NUMBER,Token::TYPE_NUMBER,$this->operators[$token->getValue()](array_reverse($params), $scale)));
        }
        if ($queue->count() !== 1) {
            throw new RuntimeException(sprintf('The expression %s is invalid.', $expression));
        }
        return $queue->pop()->getValue();
    }

    /**
     * @param string $expression
     * @param array $arguments
     * @return array
     */
    public function analyseRpnExpression(string $expression,array $arguments = []): array
    {
        $numStack   = new SplStack();
        $operaStack = new SplStack();
        $tokens     = Lexer::new($expression,$arguments)->analyse();
        /** @var Token $token */
        foreach ($tokens as $key => $token) {
            if ($token->isNumber()) {
                $numStack->push($token);
                continue;
            }
            $tokenVal = $token->getValue();
            if ($tokenVal == '(') {
                $operaStack->push($token);
                continue;
            }
            if ($tokenVal == ')') {
                while (!$operaStack->isEmpty()) {
                    /** @var Token $operaVal */
                    $operaVal = $operaStack->pop();
                    if ($operaVal->getValue() == '(') {
                        break;
                    }
                    $numStack->push($operaVal);
                }
                continue;
            }
            if ($tokenVal === '-' && ($key === 0 || (isset($tokens[$key - 1]) && in_array($tokens[$key - 1]->getValue(), ['+', '-', '*', '/', '('])))) {
                $numStack->push($tokenVal);
                $numStack->push($tokens[$key + 1]);
                unset($tokens[$key + 1]);
                continue;
            }
            if (in_array($tokenVal, ['-', '+'])) {
                while (!$operaStack->isEmpty() && in_array($operaStack->top()->getValue(), ['+', '-', '*', '/'])) {
                    $numStack->push($operaStack->pop());
                }
            }
            $operaStack->push($token);
        }
        while (!$operaStack->isEmpty()) {
            $numStack->push($operaStack->pop());
        }
        $tokens = [];
        while (!$numStack->isEmpty()) {
            $tokens[] = $numStack->shift();
        }
        return $tokens;
    }

    /**
     * @param string $expression
     * @param array $arguments
     * @return string
     */
    public function toRpnExpression(string $expression,array $arguments = []): string
    {
        return implode(' ',array_map(fn($item) => $item->getValue(),$this->analyseRpnExpression($expression,$arguments)));
    }

    /**
     * @return \Closure[]
     */
    protected function getDefaultOperators(): array
    {
        return [
            '+' => function (array $parameters, int $scale): string {
                $parameters[] = $scale;
                return bcadd(...$parameters);
            },
            '-' => function (array $parameters, int $scale): string {
                $parameters[] = $scale;
                return bcsub(...$parameters);
            },
            '*' => function (array $parameters, int $scale): string {
                $parameters[] = $scale;
                return bcmul(...$parameters);
            },
            '/' => function (array $parameters, int $scale): string {
                $parameters[] = $scale;
                return bcdiv(...$parameters);
            }
        ];
    }
}