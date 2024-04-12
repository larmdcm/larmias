<?php

declare(strict_types=1);

namespace Larmias\Validation;

use IteratorAggregate;
use ArrayIterator;
use Traversable;
use Closure;
use function array_merge;
use function is_string;
use function explode;
use function is_array;

class Rules implements IteratorAggregate
{
    /**
     * @var array
     */
    protected array $rules = [];

    /**
     * @param string|array $items
     */
    public function __construct(protected string|array $items)
    {
        $this->parseRule();
    }

    /**
     * 合并规则
     * @param Rules|array $rules
     * @return self
     */
    public function merge(Rules|array $rules): self
    {
        if ($rules instanceof Rules) {
            $rules = $rules->getRules();
        }
        $this->rules = array_merge($this->rules, $rules);
        return $this;
    }

    /**
     * 判断规则是否存在
     * @param string|array $name
     * @return bool
     */
    public function has(string|array $name): bool
    {
        if (!is_array($name)) {
            $name = [$name];
        }

        foreach ($name as $item) {
            if (isset($this->rules[$item])) {
                return true;
            }
        }

        return false;
    }

    /**
     * 获取规则列表
     * @return Rule[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * 解析规则
     * 'required|between:1,2' | ['required','length:6','between' => [1,2]]
     * @return void
     */
    protected function parseRule(): void
    {
        $items = is_string($this->items) ? explode('|', $this->items) : $this->items;
        foreach ($items as $key => $value) {
            if (is_string($value)) {
                $value = explode(':', $value, 2);
                $key = $value[0];
                $value = isset($value[1]) ? explode(',', $value[1]) : [];
            }

            if (is_array($value) || $value instanceof Closure) {
                $value = new Rule($key, $value);
            }

            if ($value instanceof Rule) {
                $this->rules[$value->getName()] = $value;
            }
        }
    }

    #[\ReturnTypeWillChange]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->rules);
    }
}