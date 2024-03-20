<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Message;

use Stringable;
use function json_decode;
use function json_encode;

class Command implements Stringable
{
    /**
     * @var string
     */
    public const COMMAND_PING = 'ping';

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
    public const COMMAND_STR = 'str';

    /**
     * @var string
     */
    public const COMMAND_CHANNEL = 'channel';

    /**
     * @var string
     */
    public const COMMAND_QUEUE = 'queue';

    /**
     * @param string $name
     * @param array $args
     */
    public function __construct(public string $name, public array $args = [])
    {
    }

    /**
     * @param string $name
     * @param array $args
     * @return string
     */
    public static function build(string $name, array $args = []): string
    {
        $command = new static($name, $args);

        return $command->toString();
    }

    /**
     * @param string $raw
     * @return Command
     */
    public static function parse(string $raw): Command
    {
        $data = json_decode($raw, true);

        return new static($data['name'], $data['args']);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'args' => $this->args,
        ];
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}