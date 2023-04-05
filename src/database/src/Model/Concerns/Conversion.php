<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Concerns;

use Larmias\Contracts\CollectionInterface;
use Larmias\Database\Model\Collection;
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
        $check = $result instanceof CollectionInterface || is_array($result);

        if (!$check) {
            return $result;
        }

        if ($result instanceof CollectionInterface) {
            $collect = new Collection($result);
            return $collect->map(function ($item) {
                return $this->toResult($item);
            });
        }

        $model = new static($result);
        $model->setExists(true);
        return $model;
    }
}