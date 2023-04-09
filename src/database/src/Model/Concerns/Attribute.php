<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Concerns;

use InvalidArgumentException;
use Larmias\Utils\Str;
use function array_udiff_assoc;
use function is_object;
use function method_exists;
use function array_key_exists;
use function is_numeric;
use function strtotime;
use function date;
use function json_encode;
use function serialize;
use function str_contains;

trait Attribute
{
    /**
     * @var array
     */
    protected array $data = [];

    /**
     * @var array
     */
    protected array $origin = [];

    /**
     * @var array
     */
    protected array $cast = [];

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setAttribute(string $name, mixed $value): void
    {
        $name = $this->getRealAttrName($name);
        $method = 'set' . Str::studly($name) . 'Attr';

        if (method_exists($this, $method)) {
            $value = $this->{$method}($value, $name);
        }

        if (isset($this->cast[$name])) {
            $value = $this->castValue($this->cast[$name], $value);
        }

        $this->data[$name] = $value;
    }

    /**
     * @param array $data
     * @return void
     */
    public function setAttributes(array $data): void
    {
        foreach ($data as $name => $value) {
            $this->setAttribute($name, $value);
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getAttribute(string $name, bool $strict = true): mixed
    {
        $name = $this->getRealAttrName($name);
        $method = 'get' . Str::studly($name) . 'Attr';

        if (method_exists($this, $method)) {
            $value = $this->{$method}($name);
        } else {
            if (!array_key_exists($name, $this->data)) {
                if (!$strict) {
                    return null;
                }
                throw new InvalidArgumentException('property not exists:' . static::class . '->' . $name);
            }

            $value = $this->data[$name];
        }

        if (isset($this->cast[$name])) {
            $value = $this->castValue($this->cast[$name], $value, true);
        }

        return $value;
    }

    /**
     * @param string $type
     * @param mixed $value
     * @param bool $isGet
     * @return mixed
     */
    protected function castValue(string $type, mixed $value, bool $isGet = false): mixed
    {
        if ($value === null) {
            return null;
        }

        switch ($type) {
            case 'integer':
                $value = (int)$value;
                break;
            case 'float':
                $value = (float)$value;
                break;
            case 'boolean':
                $value = (bool)$value;
                break;
            case 'timestamp':
                if (!is_numeric($value)) {
                    $value = strtotime($value);
                }
                break;
            case 'datetime':
                $value = is_numeric($value) ? $value : strtotime($value);
                $value = date('Y-m-d H:i:s.u', $value);
                break;
            case 'object':
                if ($isGet) {
                    $value = empty($value) ? new \stdClass() : json_decode($value);
                } else {
                    if (is_object($value)) {
                        $value = json_encode($value, JSON_FORCE_OBJECT);
                    }
                }
                break;
            case 'array':
                if ($isGet) {
                    $value = empty($value) ? [] : json_decode($value, true);
                } else {
                    $value = (array)$value;
                }
                break;
            case 'json':
                if ($isGet) {
                    $value = json_decode($value, true);
                } else {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                }
                break;
            case 'serialize':
                $value = $isGet ? unserialize($value) : serialize($value);
                break;
            default:
                if ($isGet) {
                    if (str_contains($type, '\\')) {
                        $value = new $type($value);
                    }
                } else {
                    if (is_object($value) && str_contains($type, '\\') && $value instanceof \Stringable) {
                        $value = $value->__toString();
                    }
                }
        }

        return $value;
    }

    /**
     * @return array
     */
    protected function getChangedData(): array
    {
        return array_udiff_assoc($this->data, $this->origin, function ($a, $b) {
            if ((empty($a) || empty($b)) && $a !== $b) {
                return 1;
            }

            return is_object($a) || $a != $b ? 1 : 0;
        });
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getRealAttrName(string $name): string
    {
        return Str::snake($name);
    }
}