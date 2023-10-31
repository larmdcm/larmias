<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Concerns;

use Larmias\Database\Model\Contracts\CollectionInterface;
use Larmias\Database\Model\Model;
use Larmias\Database\Query\BaseQuery;
use Larmias\Collection\Arr;
use Larmias\Database\Model\Collection as ModelCollection;
use function array_shift;
use function explode;

trait ModelRelationQuery
{
    /**
     * @var array
     */
    protected array $modelOptions = [];

    /**
     * @var Model
     */
    protected Model $model;

    /**
     * 设置查询模型
     * @param Model $model
     * @return BaseQuery|ModelRelationQuery
     */
    public function setModel(Model $model): self
    {
        $this->model = $model;
        return $this;
    }

    /**
     * 获取查询模型
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * 关联预载入In方式
     * @param array|string $with
     * @return BaseQuery|ModelRelationQuery
     */
    public function with(array|string $with): self
    {
        $this->modelOptions['with'] = $with;
        return $this;
    }

    /**
     * 是否设置了关联预载入
     * @return bool
     */
    protected function isModelWithSet(): bool
    {
        return !empty($this->modelOptions['with']);
    }

    /**
     * 预载入查询
     * @param CollectionInterface $resultSet
     * @return CollectionInterface
     */
    protected function modelWithQuery(CollectionInterface $resultSet): CollectionInterface
    {
        $with = $this->parseModelWith($this->modelOptions['with']);

        foreach ($with as $relation => $option) {
            $this->model->{$relation}()->eagerlyResultSet($resultSet, $relation, $option);
        }

        return $resultSet;
    }

    /**
     * 解析with
     * user|['user']|user.userInfo|['user' => ['userInfo']]
     * @param mixed $with
     * @return array
     */
    protected function parseModelWith(string|array $with): array
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

    /**
     * 返回模型集合
     * @param array $items
     * @return CollectionInterface
     */
    public function toModelCollection(array $items): CollectionInterface
    {
        $resultSet = new ModelCollection($items);
        $resultSet = $resultSet->map(function ($item) {
            if ($item instanceof $this->model) {
                return $item;
            }
            return $this->model::new()->data($item);
        });

        if ($this->isModelWithSet()) {
            return $this->modelWithQuery($resultSet);
        }

        return $resultSet;
    }
}