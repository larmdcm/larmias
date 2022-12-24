<?php

declare(strict_types=1);

namespace Larmias\ShareMemory\Message;

use Stringable;

class Command implements Stringable
{
    /**
     * @var string
     */
    public const MAP = 'map';

    public function __construct(public string $name, public array $args = [])
    {
    }

    public static function build(string $name, array $args = []): string
    {
        $command = new static($name, $args);
        return $command->toString();
    }

    public static function parse(string $data): Command
    {
        $data = \unserialize($data);

        return new static($data['name'], $data['args']);
    }


    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'args' => $this->args,
        ];
    }

    public function toString(): string
    {
        return \serialize($this->toArray());
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}