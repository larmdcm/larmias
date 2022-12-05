<?php

declare(strict_types=1);

namespace Larmias\Console\Input;

class Argument
{
    /**
     * 必传参数
     *
     * @var int
     */
    public const REQUIRED = 1;

    /**
     * 可选参数
     *
     * @var int
     */
    public const OPTIONAL = 2;

    /**
     * @param string $name
     * @param int $mode
     * @param string $description
     * @param mixed|null $default
     */
    public function __construct(
        protected string $name,
        protected int $mode = self::REQUIRED,
        protected string $description = '',
        protected mixed $default = null)
    {
        if (empty($this->name)) {
            throw new \InvalidArgumentException('An argument name cannot be empty.');
        }
        if ($this->mode > 2 || $this->mode < 1) {
            throw new \InvalidArgumentException(sprintf('Argument mode "%s" is not valid.', $this->mode));
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
    public function isRequired(): bool
    {
        return $this->mode === self::REQUIRED;
    }
}