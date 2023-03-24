<?php

declare(strict_types=1);

namespace Larmias\Database\Query;

use Larmias\Database\Contracts\ConnectionInterface;
use Larmias\Database\Contracts\ExpressionInterface;
use Larmias\Database\Contracts\SqlPrepareInterface;
use Larmias\Database\Contracts\BuilderInterface;
use Larmias\Database\Contracts\QueryInterface;
use function sprintf;
use function str_replace;
use function implode;
use function is_numeric;
use function is_array;
use function explode;
use function strtoupper;
use function array_map;

abstract class Builder implements BuilderInterface
{
    /**
     * @var string
     */
    protected string $selectSql = 'SELECT<FIELD> FROM <TABLE><JOIN><WHERE><GROUP><HAVING><ORDER><LIMIT>';

    /**
     * @var array
     */
    protected array $binds = [];

    /**
     * @param ConnectionInterface $connection
     */
    public function __construct(protected ConnectionInterface $connection)
    {
    }

    /**
     * @param mixed $value
     * @return void
     */
    public function bind(mixed $value): void
    {
        if (is_array($value)) {
            $this->binds = array_merge($this->binds, $value);
        } else {
            $this->binds[] = $value;
        }
    }

    /**
     * @param QueryInterface $query
     * @return SqlPrepareInterface
     */
    public function select(QueryInterface $query): SqlPrepareInterface
    {
        $options = $query->getOptions();
        $sql = str_replace(['<FIELD>', '<TABLE>', '<JOIN>', '<WHERE>', '<GROUP>', '<HAVING>', '<ORDER>', '<LIMIT>'], [
            $this->parseField($options['field']),
            $this->parseTable($options['table'], $options['alias']),
            $this->parseJoin($options['join'], $options['alias']),
            $this->parseWhere($options['where']),
            $this->parseGroup($options['group']),
            $this->parseHaving($options['having']),
            $this->parseOrder($options['order']),
            $this->parseLimit($options['limit'], $options['offset']),
        ], $this->selectSql);
        $sqlPrepare = new SqlPrepare($sql, $this->binds);
        $this->binds = [];
        return $sqlPrepare;
    }

    /**
     * @param array $fields
     * @return string
     */
    public function parseField(array $fields): string
    {
        $values = [];
        foreach ($fields as $field) {
            if ($field instanceof ExpressionInterface) {
                $values[] = $this->escape($this->parseExpression($field));
            } else {
                if (!is_array($field)) {
                    $field = explode(',', (string)$field);
                }
                foreach ($field as $key => $value) {
                    if (!is_numeric($key)) {
                        $values[] = $this->buildAlias($value, $key);
                    } else {
                        $values[] = $this->buildAlias($value);
                    }
                }
            }
        }

        return empty($values) ? '*' : implode(',', $values);
    }

    /**
     * @param string|array $table
     * @param array $alias
     * @return string
     */
    public function parseTable(string|array $table, array $alias = []): string
    {
        if (is_array($table)) {
            $values = [];
            foreach ($table as $key => $val) {
                if (is_numeric($key)) {
                    $values[] = $this->parseTable((string)$val, $alias);
                } else {
                    $values[] = $this->buildAlias($key, $val);
                }
            }
            return implode(',', $values);
        }

        return $this->buildAlias($table, $alias[$table] ?? '');
    }

    /**
     * @param array $joins
     * @param array $alias
     * @return string
     */
    public function parseJoin(array $joins, array $alias = []): string
    {
        $values = [];
        foreach ($joins as $join) {
            [$table, $on, $type] = $join;
            $table = $this->parseTable($table, $alias);
            if (str_contains($on, '=')) {
                $onSplit = explode('=', $on, 2);
                $on = $this->escape($onSplit[0]) . ' = ' . $this->escape($onSplit[1]);
            }
            $values[] = $this->buildJoin($table, $on, $type);
        }
        return implode('', $values);
    }

    /**
     * @param array $where
     * @return string
     */
    public function parseWhere(array $where): string
    {
        $values = [];
        foreach ($where as $logic => $wheres) {
            foreach ($wheres as $whereItem) {
                $condition = $this->parseWhereItem($whereItem);
                $values[] = empty($values) ? $this->buildWhere($condition) : $this->buildWhereLogic($condition, $logic);
            }
        }
        return implode('', $values);
    }

    /**
     * @param array $groups
     * @return string
     */
    public function parseGroup(array $groups): string
    {
        $values = [];
        foreach ($groups as $group) {
            if ($group instanceof ExpressionInterface) {
                $values[] = $this->parseExpression($group);
            } else if (is_array($group)) {
                foreach ($group as $item) {
                    $values[] = $this->escape($item);
                }
            } else {
                $values[] = $this->escape((string)$group);
            }
        }

        if (empty($values)) {
            return '';
        }

        return $this->buildGroup(implode(',', $values));
    }

    /**
     * @param array $orders
     * @return string
     */
    public function parseOrder(array $orders): string
    {
        $values = [];

        foreach ($orders as $order) {
            if ($order instanceof ExpressionInterface) {
                $values[] = $this->parseExpression($order);
            } else if (is_array($order)) {
                foreach ($order as $key => $value) {
                    if (str_contains($key, ',')) {
                        $key = implode(',', array_map(fn($item) => $this->escape($item), explode(',', $key)));
                    }
                    $values[] = $this->escape($key) . ' ' . $value;
                }
            } else {
                $values[] = $order;
            }
        }

        if (empty($values)) {
            return '';
        }

        return $this->buildOrder(implode(',', $values));
    }

    /**
     * @param array $having
     * @return string
     */
    public function parseHaving(array $having): string
    {
        $values = [];
        foreach ($having as $logic => $items) {
            foreach ($items as $item) {
                if ($item instanceof ExpressionInterface) {
                    $value = $this->parseExpression($item);
                } else {
                    $value = (string)$item;
                }
                $values[] = empty($values) ? $this->buildHaving($value) : $this->buildHavingLogic($value, $logic);
            }
        }
        return implode('', $values);
    }

    /**
     * @param int|null $limit
     * @param int|null $offset
     * @return string
     */
    public function parseLimit(?int $limit, ?int $offset = null): string
    {
        if (!$limit && !$offset) {
            return '';
        }

        return $this->buildLimit($limit, $offset);
    }

    /**
     * @param array $where
     * @return string
     */
    public function parseWhereItem(mixed $where): string
    {
        if ($where instanceof ExpressionInterface) {
            $this->bind($where->getBinds());
            return $where->getValue();
        }

        return $this->parseWhereOp($where);
    }

    /**
     * @param array $where
     * @return string
     */
    public function parseWhereOp(array $where): string
    {
        [$field, $op, $value] = $where;
        $this->bind($value);
        return sprintf('%s %s ?', $this->escape($field), $op);
    }

    /**
     * @param ExpressionInterface $expression
     * @return string
     */
    public function parseExpression(ExpressionInterface $expression): string
    {
        $this->bind($expression->getBinds());
        return $expression->getValue();
    }

    /**
     * @param string $field
     * @param string $alias
     * @return string
     */
    public function buildAlias(string $field, string $alias = ''): string
    {
        if (empty($alias)) {
            return $this->escape($field);
        }

        return sprintf('%s %s', $this->escape($field), $this->escape($alias));
    }

    /**
     * @param string $condition
     * @return string
     */
    public function buildWhere(string $condition = ''): string
    {
        return ' WHERE ' . $condition;
    }

    /**
     * @param string $condition
     * @param string $logic
     * @return string
     */
    public function buildWhereLogic(string $condition = '', string $logic = 'AND'): string
    {
        return sprintf(' %s %s', $logic, $condition);
    }

    /**
     * @param string $condition
     * @return string
     */
    public function buildHaving(string $condition = ''): string
    {
        return ' Having ' . $condition;
    }

    /**
     * @param string $condition
     * @param string $logic
     * @return string
     */
    public function buildHavingLogic(string $condition = '', string $logic = 'AND'): string
    {
        return sprintf(' %s %s', $logic, $condition);
    }

    /**
     * @param string $table
     * @param string $condition
     * @param string $joinType
     * @return string
     */
    public function buildJoin(string $table, string $condition, string $joinType = 'INNER'): string
    {
        return sprintf(' %s JOIN %s ON %s', strtoupper($joinType), $table, $condition);
    }

    /**
     * @param string $value
     * @return string
     */
    public function buildGroup(string $value): string
    {
        return sprintf(' GROUP BY %s', $value);
    }

    /**
     * @param string $value
     * @return string
     */
    public function buildOrder(string $value): string
    {
        return sprintf(' ORDER BY %s', $value);
    }

    /**
     * @param int $limit
     * @param int|null $offset
     * @return string
     */
    public function buildLimit(int $limit, ?int $offset = null): string
    {
        return ' LIMIT ' . ($offset ? ($offset . ',' . $limit) : $limit);
    }

    /**
     * @param string $str
     * @return string
     */
    public function escape(string $str): string
    {
        return $str;
    }
}