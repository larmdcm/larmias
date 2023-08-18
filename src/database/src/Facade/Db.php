<?php

declare(strict_types=1);

namespace Larmias\Database\Facade;

use Larmias\Contracts\CollectionInterface;
use Larmias\Contracts\PaginatorInterface;
use Larmias\Database\Contracts\ManagerInterface;
use Larmias\Database\Contracts\QueryInterface;
use Larmias\Database\Contracts\TransactionInterface;
use Larmias\Facade\AbstractFacade;
use Larmias\Database\Contracts\ExpressionInterface;

/**
 * @method static QueryInterface table(string|array $name)
 * @method static mixed getTable()
 * @method static string getName()
 * @method static QueryInterface alias(string|array $name)
 * @method static QueryInterface name(string $name)
 * @method static QueryInterface fieldRaw(string $field, array $bindings = [])
 * @method static QueryInterface field(string|array|ExpressionInterface $field)
 * @method static QueryInterface where(mixed $field, mixed $op = null, mixed $value = null, string $logic = 'AND')
 * @method static QueryInterface orWhere(mixed $field, mixed $op = null, mixed $value = null)
 * @method static QueryInterface whereRaw(string $expression, array $bindings = [])
 * @method static QueryInterface whereNull(string $field, string $logic = 'AND')
 * @method static QueryInterface whereNotNull(string $field, string $logic = 'AND')
 * @method static QueryInterface whereIn(string $field, mixed $value, string $logic = 'AND')
 * @method static QueryInterface whereNotIn(string $field, mixed $value, string $logic = 'AND')
 * @method static QueryInterface whereBetween(string $field, mixed $value, string $logic = 'AND')
 * @method static QueryInterface whereNotBetween(string $field, mixed $value, string $logic = 'AND')
 * @method static QueryInterface whereLike(string $field, mixed $value, string $logic = 'AND')
 * @method static QueryInterface whereNotLike(string $field, mixed $value, string $logic = 'AND')
 * @method static QueryInterface whereExists(string $field, mixed $value, string $logic = 'AND')
 * @method static QueryInterface whereNotExists(string $field, mixed $value, string $logic = 'AND')
 * @method static QueryInterface whereColumn(string $field, mixed $value, string $logic = 'AND')
 * @method static QueryInterface when(mixed $condition, mixed $query, mixed $otherwise = null)
 * @method static QueryInterface join(array|string $table, mixed $condition, string $joinType = 'INNER')
 * @method static QueryInterface innerJoin(array|string $table, mixed $condition)
 * @method static QueryInterface leftJoin(array|string $table, mixed $condition)
 * @method static QueryInterface rightJoin(array|string $table, mixed $condition)
 * @method static QueryInterface groupBy(array|string $field)
 * @method static QueryInterface groupByRaw(string $expression, array $bindings = [])
 * @method static QueryInterface orderBy(array|string $field, ?string $order = null)
 * @method static QueryInterface orderByRaw(string $expression, array $bindings = [])
 * @method static QueryInterface having(string $expression, array $bindings = [])
 * @method static QueryInterface orHaving(string $expression, array $bindings = [])
 * @method static QueryInterface offset(int $offset)
 * @method static QueryInterface limit(int $limit)
 * @method static QueryInterface page(int $page, int $listRows = 25)
 * @method static QueryInterface incr(string $field, float $step)
 * @method static QueryInterface decr(string $field, float $step)
 * @method static QueryInterface useSoftDelete(string $field, array $condition)
 * @method static QueryInterface lockForUpdate()
 * @method static QueryInterface sharedLock()
 * @method static QueryInterface union(mixed $union, bool $all = false)
 * @method static QueryInterface unionAll(mixed $union)
 * @method static QueryInterface distinct(bool $distinct = true)
 * @method static QueryInterface forceIndex(string $index)
 * @method static QueryInterface comment(string $comment)
 * @method static ExpressionInterface raw(string $sql, array $bindings = [])
 * @method static int execute(string $sql, array $bindings = [])
 * @method static int query(string $sql, array $bindings = [])
 * @method static string buildSql(int $buildType = QueryInterface::BUILD_SQL_SELECT, bool $sub = false)
 * @method static int count(string $field = '*')
 * @method static float sum(string $field)
 * @method static float min(string $field)
 * @method static float max(string $field)
 * @method static float avg(string $field)
 * @method static int insert(?array $data = null)
 * @method static string insertGetId(?array $data = null)
 * @method static int insertAll(?array $data = null)
 * @method static int update(?array $data = null, mixed $condition = null)
 * @method static CollectionInterface get()
 * @method static array first()
 * @method static array firstOrFail()
 * @method static array find(int|string $id)
 * @method static array findOrFail(int|string $id)
 * @method static mixed value(string $name, mixed $default = null)
 * @method static CollectionInterface pluck(string $value, ?string $key = null)
 * @method static PaginatorInterface paginate(int $perPage = 25, string $pageName = 'page', ?int $page = null, array $config = [])
 * @method static bool chunk(int $count, callable $callback, string $column = 'id', string $order = 'asc')
 * @method static TransactionInterface beginTransaction()
 * @method static mixed transaction(\Closure $callback)
 */
class Db extends AbstractFacade
{
    /**
     * @return string|object
     */
    public static function getFacadeAccessor(): string|object
    {
        return ManagerInterface::class;
    }
}