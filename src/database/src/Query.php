<?php

declare(strict_types=1);

namespace Larmias\Database;

use Larmias\Database\Contracts\ConnectionInterface;
use Larmias\Database\Contracts\ExpressionInterface;
use Larmias\Database\Contracts\QueryInterface;
use Larmias\Database\Query\Expression;
use Larmias\Database\Query\SqlGenerator;
use Larmias\Utils\Collection;
use function is_string;
use function is_array;
use function preg_match;
use function explode;
use function array_map;
use function array_unique;

class Query implements QueryInterface
{
    /**
     * @var array
     */
    protected array $options = [
        'table' => '',
        'alias' => [],
        'field' => [],
        'where' => [],
    ];

    /**
     * @var SqlGenerator
     */
    protected SqlGenerator $sqlGenerator;

    /**
     * @param ConnectionInterface $connection
     */
    public function __construct(protected ConnectionInterface $connection)
    {
        $this->sqlGenerator = new SqlGenerator($this);
    }

    /**
     * 设置表名称
     * @param string $name
     * @return QueryInterface
     */
    public function table(string $name): QueryInterface
    {
        $this->options['table'] = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->options['table'];
    }

    /**
     * 设置表别名
     * @param string|array $name
     * @return QueryInterface
     */
    public function alias(string|array $name): QueryInterface
    {
        if (is_string($name)) {
            $name = [$this->getTable() => $name];
        }
        $this->options['alias'] = $name;
        return $this;
    }

    /**
     * @param string $name
     * @return QueryInterface
     */
    public function name(string $name): QueryInterface
    {
        return $this->table($this->connection->getConfig('prefix', '') . $name);
    }

    /**
     * @param string $field
     * @param array $binds
     * @return QueryInterface
     */
    public function fieldRaw(string $field, array $binds = []): QueryInterface
    {
        $this->options['field'] = new Expression($field, $binds);
        return $this;
    }

    /**
     * @param string|array|ExpressionInterface $field
     * @return QueryInterface
     */
    public function field(string|array|ExpressionInterface $field): QueryInterface
    {
        if ($field instanceof ExpressionInterface) {
            $this->options['field'] = $field;
            return $this;
        }

        if (is_string($field)) {
            if (preg_match('/[\<\'\"\(]/', $field)) {
                return $this->fieldRaw($field);
            }
            $field = array_map('trim', explode(',', $field));
        }
        $this->options['field'] = array_unique($field);
        return $this;
    }

    /**
     * @return Collection
     */
    public function get(): Collection
    {
        $sqlPrepare = $this->sqlGenerator->select();
        $items = $this->connection->query($sqlPrepare->getSql(), $sqlPrepare->getBinds());
        return Collection::make($items);
    }
}