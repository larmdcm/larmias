<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Concerns;

use Larmias\Contracts\CollectionInterface;
use Larmias\Database\Model\Collection;
use Larmias\Utils\Contracts\Arrayable;
use function is_array;
use function json_encode;

trait Conversion
{
    /**
     * @return array
     */
    public function toArray(): array
    {
        $data = $this->data;
        foreach ($data as $name => $value) {
            $data[$name] = $this->getAttribute($name);
        }
        return $data;
    }

    /**
     * @param int $options
     * @return string
     */
    public function toJson(int $options = JSON_UNESCAPED_UNICODE): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    // JsonSerializable
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @param mixed $result
     * @return mixed
     */
    public function toResult(mixed $result): mixed
    {
        if (!$this->isResultSet($result)) {
            return $result;
        }

        if ($result instanceof CollectionInterface) {
            $collect = new Collection($result);
            return $collect->map(function ($item) {
                return $this->toResult($item);
            });
        } else if ($result instanceof Arrayable) {
            $result = $result->toArray();
        }

        $model = new static($result);
        $model->setExists(true);
        return $model;
    }

    /**
     * @param mixed $result
     * @return bool
     */
    protected function isResultSet(mixed $result): bool
    {
        return $result instanceof CollectionInterface || $result instanceof Arrayable || is_array($result);
    }
}