<?php

declare(strict_types=1);

namespace Larmias\Database\Query\Concerns;

use Larmias\Database\Contracts\ModelCollectionInterface;
use Larmias\Database\Contracts\ModelInterface;
use Larmias\Database\Query\QueryBuilder;
use Larmias\Utils\Arr;
use Larmias\Database\Model\Collection as ModelCollection;
use function array_shift;
use function explode;

/**
 * @mixin QueryBuilder
 */
trait ModelRelationQuery
{
    /**
     * @var array
     */
    protected array $modelOptions = [];

    /**
     * @var ModelInterface|null
     */
    protected ?ModelInterface $model = null;

    /**
     * 设置查询模型
     * @param ModelInterface $model
     * @return QueryBuilder|ModelRelationQuery
     */
    public function setModel(ModelInterface $model): self
    {
        $this->model = $model;
        return $this;
    }

    /**
     * 关联预载入In方式
     * @param array|string $with
     * @return QueryBuilder|ModelRelationQuery
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
     * @param ModelCollectionInterface $resultSet
     * @return ModelCollectionInterface
     */
    protected function modelWithQuery(ModelCollectionInterface $resultSet): ModelCollectionInterface
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
     * @return ModelCollectionInterface
     */
    public function toModelCollection(array $items): ModelCollectionInterface
    {
        $resultSet = new ModelCollection($items);
        $resultSet = $resultSet->map(function ($item) {
            return $this->model::new($item);
        });

        if ($this->isModelWithSet()) {
            return $this->modelWithQuery($resultSet);
        }

        return $resultSet;
    }

    /**
     * 是否可返回模型集合
     * @return bool
     */
    protected function isToModelCollection(): bool
    {
        return $this->model !== null;
    }
}