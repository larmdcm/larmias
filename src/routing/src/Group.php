<?php

declare(strict_types=1);

namespace Larmias\Routing;

use function Larmias\Utils\is_empty;

class Group
{
    /**
     * @var integer
     */
    protected int $groupNumber = -1;

    /**
     * @var array
     */
    protected array $options = [];

    /**
     * @var array
     */
    protected array $groupNumbers = [];

    /**
     * create group.
     *
     * @param callable|null $callback
     * @return void
     */
    public function create(array $option = [], ?callable $callback = null): void
    {
        $this->groupNumber++;
        $groupNumber = $this->groupNumber;
        $this->groupNumbers[] = $groupNumber;
        $this->options[$groupNumber] = \array_merge(Rule::getDefaultOption(), $option);
        \is_callable($callback) && \call_user_func($callback, $this->groupNumbers);

        if (\in_array($groupNumber, $this->groupNumbers, true)) {
            $index = \array_search($groupNumber, $this->groupNumbers);
            \array_splice($this->groupNumbers, $index, 1);
        }
    }

    /**
     * 设置选项.
     *
     * @param integer $groupNumber
     * @param array $option
     * @return self
     */
    public function setOption(int $groupNumber, array $option): self
    {
        if (isset($this->options[$groupNumber])) {
            $this->options[$groupNumber] = \array_merge($this->options[$groupNumber], $option);
        }
        return $this;
    }

    /**
     * 获取选项.
     *
     * @param array $groupNumbers
     * @return array
     */
    public function getOption(array $groupNumbers): array
    {
        $params = [];
        if (!empty($groupNumbers)) {
            foreach ($this->options as $key => $value) {
                if (!\in_array($key, $groupNumbers) || empty($value)) {
                    continue;
                }
                $params = static::mergeOption($params, $value);
            }
        }
        return $params;
    }

    /**
     * 选项合并.
     *
     * @param array $first
     * @param array $second
     * @return array
     */
    public static function mergeOption(array $first, array $second): array
    {
        if (empty($first)) {
            $first = \array_filter($second, fn($value) => !is_empty($value));
        } else {
            foreach ($second as $key => $value) {
                if (is_empty($value)) {
                    continue;
                }
                if (!isset($first[$key])) {
                    $first[$key] = [];
                } else {
                    if (!is_array($first[$key])) {
                        $first[$key] = [$first[$key]];
                    }
                }
                $first[$key] = \array_merge($first[$key], \is_array($value) ? $value : [$value]);
            }
        }
        return $first;
    }

    /**
     * Get the value of groupNumbers
     *
     * @return array
     */
    public function getGroupNumbers(): array
    {
        return $this->groupNumbers;
    }
}