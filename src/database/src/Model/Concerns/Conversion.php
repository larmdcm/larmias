<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Concerns;

use Larmias\Database\Model\Contracts\CollectionInterface;
use Larmias\Database\Model\Model;
use function json_encode;

/**
 * @mixin Model
 */
trait Conversion
{
    /**
     * @return array
     */
    public function toArray(): array
    {
        $data = array_merge($this->data, $this->relation);
        $result = [];
        foreach ($data as $name => $value) {
            if ($value instanceof Model || $value instanceof CollectionInterface) {
                $result[$name] = $value->toArray();
            } else {
                $result[$name] = $this->getAttribute($name);
            }
        }
        return $result;
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
}