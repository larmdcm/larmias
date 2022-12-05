<?php

namespace Larmias\Console\Input;

class Option
{
    /**
     * 无需传值
     *
     * @var int
     */
    public const VALUE_NONE = 1;

    /**
     * 必须传值
     *
     * @var int
     */
    public const VALUE_REQUIRED = 2;

    /**
     * 可选传值
     *
     * @var int
     */
    public const VALUE_OPTIONAL = 3;

    /**
     * @param string $name
     * @param string|null $shortcut
     * @param int $mode
     * @param string $description
     * @param mixed|null $default
     */
    public function __construct(
        protected string $name,
        protected ?string $shortcut = null,
        protected int $mode = self::VALUE_NONE,
        protected string $description = '',
        protected mixed $default = null)
    {
        if (empty($this->name)) {
            throw new \InvalidArgumentException('An option name cannot be empty.');
        }
        if ($this->mode > 3 || $this->mode < 1) {
            throw new \InvalidArgumentException(sprintf('Option mode "%s" is not valid.', $this->mode));
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getShortcut(): ?string
    {
        return $this->shortcut;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getDefault(): mixed
    {
        return $this->default;
    }

    /**
     * @return bool
     */
    public function isValueRequired(): bool
    {
        return $this->mode === self::VALUE_REQUIRED;
    }

    /**
     * @return bool
     */
    public function isValueOptional(): bool
    {
        return $this->mode === self::VALUE_OPTIONAL;
    }

    /**
     * @return bool
     */
    public function isAcceptValue(): bool
    {
        return $this->isValueRequired() || $this->isValueOptional();
    }
}