<?php

declare(strict_types=1);

namespace Larmias\Database\Query\Builder;

use Larmias\Database\Contracts\BuilderInterface;
use Larmias\Database\Contracts\ConnectionInterface;
use Larmias\Database\Contracts\ExpressionInterface;
use Larmias\Database\Contracts\SqlPrepareInterface;
use Larmias\Database\Entity\SqlPrepare;
use Larmias\Database\Exceptions\QueryException;
use Closure;
use Larmias\Utils\Contracts\Arrayable;
use function array_map;
use function array_values;
use function array_keys;
use function explode;
use function implode;
use function is_array;
use function is_numeric;
use function Larmias\Utils\is_empty;
use function str_contains;
use function sprintf;
use function str_replace;
use function strtoupper;
use function count;
use function str_repeat;
use function rtrim;
use function is_string;

abstract class Builder implements BuilderInterface
{
    /**
     * 查询语句
     * @var string
     */
    protected string $selectSql = 'SELECT <FIELD> FROM <TABLE><JOIN><WHERE><GROUP><HAVING><ORDER><LIMIT>';

    /**
     * 新增语句
     * @var string
     */
    protected string $insertSql = 'INSERT INTO <TABLE>(<FIELD>) VALUES (<DATA>)';

    /**
     * 批量新增语句
     * @var string
     */
    protected string $insertAllSql = 'INSERT INTO <TABLE>(<FIELD>) VALUES <DATA>';

    /**
     * 修改语句
     * @var string
     */
    protected string $updateSql = 'UPDATE <TABLE> SET <SET><JOIN><WHERE><ORDER><LIMIT>';

    /**
     * 删除语句
     * @var string
     */
    protected string $deleteSql = 'DELETE FROM <TABLE><JOIN><WHERE><ORDER><LIMIT>';

    /**
     * 绑定的参数
     * @var array
     */
    protected array $bindings = [];

    /**
     * @param ConnectionInterface $connection
     */
    public function __construct(protected ConnectionInterface $connection)
    {
    }

    /**
     * 绑定参数
     * @param mixed $value
     * @return void
     */
    public function bind(mixed $value): void
    {
        if (is_array($value)) {
            $this->bindings = array_merge($this->bindings, $value);
        } else {
            $this->bindings[] = $value;
        }
    }

    /**
     * 新增
     * @param array $options
     * @return SqlPrepareInterface
     */
    public function insert(array $options): SqlPrepareInterface
    {
        $data = $options['data'];
        $fields = array_keys($data);
        $sql = str_replace(['<TABLE>', '<FIELD>', '<DATA>'], [
            $this->parseTable($options['table'], $options['alias']),
            implode(',', array_map(fn($field) => $this->escapeField($field), $fields)),
            rtrim(str_repeat("?,", count($data)), ',')
        ], $this->insertSql);
        $this->bind(array_values($data));
        return $this->createSqlPrepare($sql);
    }

    /**
     * 批量新增
     * @param array $options
     * @return SqlPrepareInterface
     */
    public function insertAll(array $options): SqlPrepareInterface
    {
        $data = $options['data'];
        $fields = array_keys(current($data));
        $sqlValues = [];
        foreach ($data as $item) {
            $values = array_values($item);
            $sqlValues[] = sprintf('(%s)', rtrim(str_repeat("?,", count($values)), ','));
            $this->bind($values);
        }
        $sql = str_replace(['<TABLE>', '<FIELD>', '<DATA>'], [
            $this->parseTable($options['table']),
            implode(',', array_map(fn($field) => $this->escapeField($field), $fields)),
            implode(',', $sqlValues)
        ], $this->insertAllSql);
        return $this->createSqlPrepare($sql);
    }

    /**
     * 修改
     * @param array $options
     * @return SqlPrepareInterface
     */
    public function update(array $options): SqlPrepareInterface
    {
        $sql = str_replace(['<TABLE>', '<SET>', '<JOIN>', '<WHERE>', '<ORDER>', '<LIMIT>'], [
            $this->parseTable($options['table']),
            $this->parseUpdateSet($options['data'], $options['incr']),
            $this->parseJoin($options['join'], $options['alias']),
            $where = $this->parseWhere($options['where']),
            $this->parseOrder($options['order']),
            $this->parseLimit($options['limit']),
        ], $this->updateSql);

        if (empty($where)) {
            throw new QueryException('Update condition cannot be empty');
        }

        return $this->createSqlPrepare($sql);
    }

    /**
     * 删除
     * @param array $options
     * @return SqlPrepareInterface
     */
    public function delete(array $options): SqlPrepareInterface
    {
        $sql = str_replace(['<TABLE>', '<JOIN>', '<WHERE>', '<ORDER>', '<LIMIT>'], [
            $this->parseTable($options['table']),
            $this->parseJoin($options['join'], $options['alias']),
            $where = $this->parseWhere($options['where']),
            $this->parseOrder($options['order']),
            $this->parseLimit($options['limit']),
        ], $this->deleteSql);

        if (empty($where)) {
            throw new QueryException('Delete condition cannot be empty');
        }

        return $this->createSqlPrepare($sql);
    }

    /**
     * 查询
     * @param array $options
     * @return SqlPrepareInterface
     */
    public function select(array $options): SqlPrepareInterface
    {
        $sql = str_replace(['<FIELD>', '<TABLE>', '<JOIN>', '<WHERE>', '<GROUP>', '<HAVING>', '<ORDER>', '<LIMIT>'], [
            $this->parseField($options['field']),
            $this->parseTable($options['table'], $options['alias']),
            $this->parseJoin($options['join'], $options['alias']),
            $this->parseWhere($options['where']),
            $this->parseGroup($options['group']),
            $this->parseHaving($options['having']),
            $this->parseOrder($options['order'] ?? []),
            $this->parseLimit($options['limit'] ?? null, $options['offset'] ?? null),
        ], $this->selectSql);

        return $this->createSqlPrepare($sql);
    }

    /**
     * 聚合查询
     * @param string $type
     * @param string $field
     * @param string $name
     * @return string
     */
    public function aggregate(string $type, string $field, string $name): string
    {
        return sprintf('%s(%s)', strtoupper($type), $field) . ' ' . $this->escapeField($name);
    }

    /**
     * @param string $sql
     * @return SqlPrepareInterface
     */
    public function createSqlPrepare(string $sql): SqlPrepareInterface
    {
        $sqlPrepare = new SqlPrepare($sql, $this->bindings);
        $this->bindings = [];
        return $sqlPrepare;
    }

    /**
     * 解析字段
     * @param array $fields
     * @return string
     */
    public function parseField(array $fields): string
    {
        $values = [];
        foreach ($fields as $field) {
            if ($field instanceof ExpressionInterface) {
                $values[] = $this->parseExpression($field);
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
     * 解析表名
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
     * 解析修改字段
     * @param array $data
     * @param array $incr
     * @return string
     */
    public function parseUpdateSet(array $data, array $incr): string
    {
        $fields = array_keys($data);
        $this->bind(array_values($data));
        $set = array_map(fn($field) => $this->escapeField($field) . ' = ?', $fields);
        foreach ($incr as $item) {
            [$field, $value] = $item;
            if ($value != 0) {
                $field = $this->escapeField($field);
                $set[] = sprintf('%s = %s %s ?', $field, $field, $value > 0 ? '+' : '-');
                $this->bind(abs($value));
            }
        }

        return implode(',', $set);
    }

    /**
     * 解析JOIN
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
                $on = $this->escapeField($onSplit[0]) . ' = ' . $this->escapeField($onSplit[1]);
            }
            $values[] = $this->buildJoin($table, $on, $type);
        }
        return implode('', $values);
    }

    /**
     * 解析WHERE
     * @param array $where
     * @param bool $firstLogic
     * @return string
     */
    public function parseWhere(array $where, bool $firstLogic = true): string
    {
        $values = [];
        foreach ($where as $logic => $wheres) {
            foreach ($wheres as $whereItem) {
                $condition = $this->parseWhereItem($whereItem);
                if ($firstLogic) {
                    $values[] = empty($values) ? $this->buildWhere($condition) : $this->buildWhereLogic($condition, $logic);
                } else {
                    $values[] = $this->buildWhereLogic($condition, empty($values) ? '' : $logic);
                }
            }
        }
        return implode('', $values);
    }

    /**
     * 解析分组查询
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
                    $values[] = $this->escapeField($item);
                }
            } else {
                $values[] = $this->escapeField((string)$group);
            }
        }

        if (empty($values)) {
            return '';
        }

        return $this->buildGroup(implode(',', $values));
    }

    /**
     * 解析排序查询
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
                        $key = implode(',', array_map(fn($item) => $this->escapeField($item), explode(',', $key)));
                    }
                    $values[] = $this->escapeField($key) . ' ' . $value;
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
     * 解析HAVING
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
     * 解析LIMIT
     * @param int|null $limit
     * @param int|null $offset
     * @return string
     */
    public function parseLimit(?int $limit, ?int $offset = null): string
    {
        if (!$limit && !$offset) {
            return '';
        }

        if (!$offset) {
            $this->bind($limit);
            return $this->buildLimit('?');
        }

        $this->bind([$limit, $offset]);

        return $this->buildLimit('?', '?');
    }

    /**
     * 解析WHERE ITEM
     * @param array $where
     * @return string
     */
    public function parseWhereItem(mixed $where): string
    {
        if (is_string($where)) {
            return $where;
        }

        if ($where instanceof ExpressionInterface) {
            return $this->parseExpression($where);
        }

        if ($where instanceof Closure) {
            return '( ' . $this->parseWhere($where(), false) . ' )';
        }

        return $this->parseWhereOp($where);
    }

    /**
     * 解析WHERE OP
     * @param array $where
     * @return string
     */
    public function parseWhereOp(array $where): string
    {
        [$field, $op, $value] = $where;

        if ($value === null) {
            $value = $op;
            $op = '=';
        }

        $field = $this->escapeField($field);
        [$op, $value] = $this->optimize($op, $value);

        switch ($op) {
            case 'NULL':
            case 'IS NULL':
                return $this->buildWhereNull($field);
            case 'NOT NULL':
            case 'IS NOT NULL':
                return $this->buildWhereNotNull($field);
            case 'IN':
            case 'NOT IN':
                return $this->parseWhereIn($field, $value, $op);
            case 'BETWEEN':
            case 'NOT BETWEEN':
                return $this->parseWereBetween($field, $value, $op);
            case 'LIKE':
            case 'NOT LIKE':
                return $this->parseWhereLike($field, $value, $op);
            case 'EXISTS':
            case 'NOT EXISTS':
                return $this->parseWhereExists($field, $value, $op);
            case 'COLUMN':
                return $this->parseWhereColumn($field, $value);
            default:
                $this->bind($value);
                return sprintf('%s %s ?', $field, $op);
        }
    }

    /**
     * 解析WHERE IN
     * @param string $field
     * @param mixed $value
     * @param string $op
     * @return string
     */
    protected function parseWhereIn(string $field, mixed $value, string $op = 'IN'): string
    {
        if (is_empty($value)) {
            $value = [''];
        } else if (is_string($value)) {
            $value = explode(',', $value);
        }

        $this->bind($value);

        return $this->buildWhereIn($field, rtrim(str_repeat('?,', count($value)), ','), $op);
    }

    /**
     * 解析WHERE BETWEEN
     * @param string $field
     * @param mixed $value
     * @param string $op
     * @return string
     */
    protected function parseWereBetween(string $field, mixed $value, string $op = 'BETWEEN'): string
    {
        if (is_string($value)) {
            $value = str_contains($value, ',') ? explode(',', $value) : [$value];
        }

        $this->bind($value);

        return $this->buildWhereBetween($field, ['?', '?'], $op);
    }

    /**
     * 解析WHERE LIKE
     * @param string $field
     * @param mixed $value
     * @param string $op
     * @return string
     */
    protected function parseWhereLike(string $field, mixed $value, string $op = 'LIKE'): string
    {
        $this->bind($value);
        return $this->buildWhereLike($field, '?', $op);
    }

    /**
     * 解析WHERE EXISTS
     * @param string $field
     * @param mixed $value
     * @param string $op
     * @return string
     */
    protected function parseWhereExists(string $field, mixed $value, string $op = 'EXISTS'): string
    {
        return $this->buildWhereExists($field, $value, $op);
    }

    /**
     * 解析WHERE 字段比对
     * @param string $field
     * @param mixed $value
     * @return string
     */
    protected function parseWhereColumn(string $field, mixed $value): string
    {
        return $this->buildWhereColumn($field, $value[0], $this->escapeField($value[1]));
    }


    /**
     * 优化操作符和操作值
     * @param string $op
     * @param mixed $value
     * @return array
     */
    protected function optimize(string $op, mixed $value): array
    {
        $op = trim(strtoupper($op));

        switch ($op) {
            case '=':
            case '!=':
            case '<>':
                if ($value === null) {
                    $op = $op == '=' ? 'NULL' : 'NOT NULL';
                } else if (is_array($value) || $value instanceof Arrayable) {
                    $op = $op == '=' ? 'IN' : 'NOT IN';
                    return $this->optimize($op, $value);
                }
                break;
            case 'IN':
            case 'NOT IN':
                $value = $this->valueToArray($value);
                if (count($value) == 1) {
                    $op = $op == 'IN' ? '=' : '<>';
                    $value = current($value) ?: '';
                }
                break;
        }

        return [$op, $value];
    }

    /**
     * 转数组
     * @param mixed $value
     * @return array
     */
    protected function valueToArray(mixed $value): array
    {
        if (is_empty($value)) {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            return explode(',', $value);
        }

        if ($value instanceof Arrayable) {
            return $value->toArray();
        }

        return [$value];
    }

    /**
     * 解析原生表达式
     * @param ExpressionInterface $expression
     * @return string
     */
    public function parseExpression(ExpressionInterface $expression): string
    {
        $this->bind($expression->getBindings());
        return $expression->getValue();
    }

    /**
     * 构建字段别名
     * @param string $field
     * @param string $alias
     * @return string
     */
    public function buildAlias(string $field, string $alias = ''): string
    {
        if (empty($alias)) {
            return $this->escapeField($field);
        }

        return sprintf('%s AS %s', $this->escapeField($field), $this->escapeField($alias));
    }

    /**
     * 构建WHERE
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
        return sprintf('%s%s%s', empty($logic) ? '' : ' ', empty($logic) ? '' : $logic . ' ', $condition);
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
     * 构建LIMIT
     * @param string $limit
     * @param string|null $offset
     * @return string
     */
    public function buildLimit(string $limit, ?string $offset = null): string
    {
        return ' LIMIT ' . ($offset ? ($offset . ',' . $limit) : $limit);
    }

    /**
     * 构建WHERE NULL
     * @param string $field
     * @return string
     */
    public function buildWhereNull(string $field): string
    {
        return $field . ' IS NULL';
    }

    /**
     * 构建WHERE NOT NULL
     * @param string $field
     * @return string
     */
    public function buildWhereNotNull(string $field): string
    {
        return $field . ' IS NOT NULL';
    }

    /**
     * 构建语句WHERE IN
     * @param string $field
     * @param string $value
     * @param string $op
     * @return string
     */
    public function buildWhereIn(string $field, string $value, string $op = 'IN'): string
    {
        return sprintf('%s %s (%s)', $field, $op, $value);
    }

    /**
     * @param string $field
     * @param array $value
     * @param string $op
     * @return string
     */
    public function buildWhereBetween(string $field, array $value, string $op = 'BETWEEN'): string
    {
        return sprintf('%s %s %s AND %s', $field, $op, $value[0], $value[1]);
    }

    /**
     * @param string $field
     * @param string $value
     * @param string $op
     * @return string
     */
    public function buildWhereLike(string $field, string $value, string $op = 'LIKE'): string
    {
        return sprintf('%s %s %s', $field, $op, $value);
    }

    /**
     * @param string $field
     * @param string $value
     * @param string $op
     * @return string
     */
    public function buildWhereExists(string $field, string $value, string $op = 'EXISTS'): string
    {
        return sprintf('%s %s ( %s )', $field, $op, $value);
    }

    /**
     * @param string $field
     * @param string $value
     * @param string $op
     * @return string
     */
    public function buildWhereColumn(string $field, string $op, string $value): string
    {
        return sprintf('(%s %s %s)', $field, $op, $value);
    }

    /**
     * 转义字段
     * @param string $field
     * @return string
     */
    abstract public function escapeField(string $field): string;
}