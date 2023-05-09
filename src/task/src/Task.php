<?php

declare(strict_types=1);

namespace Larmias\Task;

use JsonSerializable;
use Opis\Closure\SerializableClosure;
use Closure;
use function session_create_id;
use function is_array;
use function is_string;
use function serialize;
use function unserialize;

class Task implements JsonSerializable
{
    /**
     * @param string|array|Closure $handler
     * @param array $args
     * @param int $priority
     * @param string|null $id
     */
    public function __construct(protected string|array|Closure $handler, protected array $args = [], protected int $priority = 1, protected ?string $id = null)
    {
        if ($this->id === null) {
            $this->id = session_create_id();
        }
    }

    /**
     * @param array $data
     * @return Task
     */
    public static function parse(array $data): Task
    {
        $handler = $data['handler'];
        if (is_array($handler) && isset($handler['type'])) {
            if ($handler['type'] === 'closure') {
                $handler = unserialize($handler['handler']);
                $handler = $handler->getClosure();
            } else {
                $handler = $handler['handler'];
            }
        }
        return new Task($handler, $data['args'], $data['priority'], $data['id']);
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return self
     */
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|array|Closure
     */
    public function getHandler(): string|array|Closure
    {
        return $this->handler;
    }

    /**
     * @param string|array|Closure $handler
     * @return self
     */
    public function setHandler(string|array|Closure $handler): self
    {
        $this->handler = $handler;
        return $this;
    }

    /**
     * @return array
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * @param array $args
     * @return self
     */
    public function setArgs(array $args): self
    {
        $this->args = $args;
        return $this;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     * @return self
     */
    public function setPriority(int $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        $data = ['type' => 'mixed', 'handler' => $this->handler];
        if (is_string($data['handler'])) {
            $data['type'] = 'string';
        } else if ($data['handler'] instanceof Closure) {
            $data['type'] = 'closure';
            $data['handler'] = serialize(new SerializableClosure($data['handler']));
        }
        return [
            'id' => $this->id,
            'handler' => $data,
            'args' => $this->args,
            'priority' => $this->priority,
        ];
    }
}