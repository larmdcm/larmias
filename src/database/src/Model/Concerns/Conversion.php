<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Concerns;

use Larmias\Database\Model;
use Larmias\Database\Model\Contracts\CollectionInterface;
use Larmias\Stringable\Str;
use function json_encode;

/**
 * @mixin Model
 */
trait Conversion
{
    /**
     * 输出隐藏的属性
     * @var array
     */
    protected array $hidden = [];

    /**
     * 输出显示的属性
     * @var array
     */
    protected array $visible = [];

    /**
     * 输出要追加的属性
     * @var array
     */
    protected array $append = [];

    /**
     * 输出映射的字段
     * @var array
     */
    protected array $mapping = [];

    /**
     * 属性输出命名转换（1：下划线 2：小驼峰 3：大驼峰）
     * @var int|null
     */
    protected ?int $convertNameType = null;

    /**
     * 设置属性输出命名转换
     * @param int|null $convertNameType
     * @return Conversion|Model
     */
    public function convertNameType(?int $convertNameType = null): self
    {
        $this->convertNameType = $convertNameType;
        return $this;
    }


    /**
     * 设置需要隐藏的输出属性
     * @param array $hidden
     * @param bool $merge
     * @return Conversion|Model
     */
    public function hidden(array $hidden = [], bool $merge = false): self
    {
        if ($merge) {
            $this->hidden = array_merge($this->hidden, $hidden);
        } else {
            $this->hidden = $hidden;
        }

        return $this;
    }


    /**
     * 设置需要输出的属性
     * @param array $visible
     * @param bool $merge
     * @return Conversion|Model
     */
    public function visible(array $visible = [], bool $merge = false): self
    {
        if ($merge) {
            $this->visible = array_merge($this->visible, $visible);
        } else {
            $this->visible = $visible;
        }

        return $this;
    }

    /**
     * 设置需要追加的属性
     * @param array $append
     * @param bool $merge
     * @return Conversion|Model
     */
    public function append(array $append = [], bool $merge = false): self
    {
        if ($merge) {
            $this->append = array_merge($this->append, $append);
        } else {
            $this->append = $append;
        }

        return $this;
    }

    /**
     * 设置属性输出的映射
     * @param array $map
     * @return Conversion|Model
     */
    public function mapping(array $map = []): self
    {
        $this->mapping = $map;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $data = array_merge($this->data, $this->relation);
        $result = [];
        foreach ($data as $key => $value) {
            if (!$this->isVisible($key)) {
                continue;
            }

            if ($value instanceof Model || $value instanceof CollectionInterface) {
                $result[$key] = $value->toArray();
            } else {
                $result[$key] = $this->getAttribute($key);
            }

            if (isset($this->mapping[$key])) {
                $mapName = $this->mapping[$key];
                $result[$mapName] = $result[$key];
                unset($result[$key]);
            }
        }

        foreach ($this->append as $name) {
            $result[$name] = $this->getAttribute($name);
        }

        if ($this->convertNameType) {
            foreach ($result as $key => $value) {
                $name = Str::convertNameByType($key, $this->convertNameType);
                if ($name !== $key) {
                    $result[$name] = $value;
                    unset($result[$key]);
                }
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
     * 是否是显示输出的属性
     * @param string $key
     * @return bool
     */
    public function isVisible(string $key): bool
    {
        if (in_array($key, $this->visible)) {
            return true;
        }
        return !$this->isHidden($key) && empty($this->visible);
    }

    /**
     * 是否是隐藏输出的属性
     * @param string $key
     * @return bool
     */
    public function isHidden(string $key): bool
    {
        return !empty($this->hidden) && in_array($key, $this->hidden);
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