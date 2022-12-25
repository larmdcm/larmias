<?php

declare(strict_types=1);

namespace Larmias\ShareMemory\Message;

use Stringable;

class Command implements Stringable
{
    /**
     * @var string
     */
    public const COMMAND_SELECT = 'select';

    /**
     * @var string
     */
    public const COMMAND_AUTH = 'auth';

    /**
     * @var string
     */
    public const COMMAND_MAP = 'map';

    public function __construct(public string $name, public array $args = [])
    {
    }

    public static function build(string $name, array $args = []): string
    {
        $command = new static($name, $args);
        return $command->toString();
    }

    public static function parse(string $raw): Command
    {
        $data = \json_decode($raw,true);

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
        return \json_encode($this->toArray(),JSON_UNESCAPED_UNICODE);
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}