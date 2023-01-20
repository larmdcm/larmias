<?php

declare(strict_types=1);

namespace Larmias\Rpn;

use Stringable;

class Token implements Stringable
{
    /** @var string */
    public const TYPE_OPERATOR = 'OPERATOR';

    /** @var string */
    public const TYPE_NUMBER = 'NUMBER';

    /** @var string */
    public const TYPE_IDENTIFY = 'IDENTIFY';

    /**
     * Token __construct.
     *
     * @param string|null $type
     * @param string|null $name
     * @param string|null $value
     */
    public function __construct(protected ?string $type = null, protected ?string $name = null, protected ?string $value = null)
    {
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     */
    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string|null $value
     */
    public function setValue(?string $value): void
    {
        $this->value = $value;
    }

    /**
     * @return bool
     */
    public function isOperator(): bool
    {
        return $this->getType() == self::TYPE_OPERATOR;
    }

    public function isNumber(): bool
    {
        return $this->getType() == self::TYPE_NUMBER || $this->getType() == self::TYPE_IDENTIFY;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf('Token{type=%s,name=%s,value=%s};', $this->type, $this->name, $this->value);
    }
}