<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Concerns;

use Larmias\Database\Model;
use Larmias\Utils\Arr;
use function explode;
use function array_shift;

/**
 * @mixin Model
 */
trait ModelRelationQuery
{
    /**
     * @var array
     */
    protected array $options = [];

    /**
     * 关联预载入In方式
     * @param array|string $with
     * @return Model|ModelRelationQuery
     */
    public function with(array|string $with): self
    {
        $this->options['with'] = $with;
        return $this;
    }

    /**
     * 是否设置了关联预载入
     * @return bool
     */
    public function isWithSet(): bool
    {
        return !empty($this->options['with']);
    }

    /**
     * 预载入查询
     * @param mixed $result
     * @return mixed
     */
    public function withQuery(mixed $result): mixed
    {
        $check = $this->isResultSet($result);
        if (!$check && !($result instanceof Model)) {
            return $result;
        }

        $resultSet = $check ? $result : new Model\Collection($result);

        $with = $this->parseWith($this->options['with']);

        foreach ($with as $relation => $option) {
            $this->{$relation}()->eagerlyResultSet($resultSet, $relation, $option);
        }

        return $check ? $resultSet : $resultSet[0];
    }

    /**
     * 解析with
     * user|['user']|user.userInfo|['user' => ['userInfo']]
     * @param mixed $with
     * @return array
     */
    protected function parseWith(string|array $with): array
    {
        $result = [];

        $with = is_string($with) ? explode(',', $with) : $with;

        $isList = Arr::isList($with);

        foreach ($with as $key => $value) {
            if ($isList) {
                $values = explode('.', $value);
                $firstVal = array_shift($values);
                $result[$firstVal] = $values;
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}