<?php

declare(strict_types=1);

namespace Larmias\Validation;

use IteratorAggregate;
use ArrayIterator;
use Traversable;
use Closure;

class Rules implements IteratorAggregate
{
    /**
     * @var array
     */
    protected array $rules = [];

    /**
     * @param string|array $rule
     */
    public function __construct(protected string|array $rule)
    {
        $this->parseRule();
    }

    /**
     * @param Rules|array $rules
     * @return self
     */
    public function merge(Rules|array $rules): self
    {
        if ($rules instanceof Rules) {
            $rules = $rules->getRules();
        }
        $this->rules = \array_merge($this->rules, $rules);
        return $this;
    }

    /**
     * @return array
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * 'required|between:1,2' | ['required','length:6',['between' => [1,2]]
     *
     * @return void
     */
    protected function parseRule(): void
    {
        $rules = \is_string($this->rule) ? \explode('|', $this->rule) : $this->rule;
        foreach ($rules as $key => $item) {
            if (\is_string($item)) {
                $item = \explode(':', $item, 2);
                $key = $item[0];
                $item = isset($item[1]) ? \explode(',', $item[1]) : [];
            }

            if (\is_array($item) || $item instanceof Closure) {
                $item = new Rule($key, $item);
            }

            if ($item instanceof Rule) {
                $this->rules[$item->getName()] = $item;
            }
        }
    }

    #[\ReturnTypeWillChange]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->rules);
    }
}