<?php

declare(strict_types=1);

namespace Larmias\Database\Connections;

use Larmias\Database\Contracts\ExecuteResultInterface;
use Larmias\Database\Entity\ExecuteResult;
use Larmias\Database\Events\QueryExecuted;
use Larmias\Database\Events\StatementPrepared;
use Larmias\Database\Events\TransactionBeginning;
use Larmias\Database\Events\TransactionCommitted;
use Larmias\Database\Events\TransactionRolledBack;
use Larmias\Database\Exceptions\BindParamException;
use Larmias\Database\Exceptions\PDOException;
use Larmias\Database\Exceptions\TransactionException;
use PDO;
use PDOStatement;
use Throwable;
use Closure;
use function is_numeric;
use function addcslashes;
use function Larmias\Support\throw_unless;
use function substr_replace;
use function strpos;
use function strlen;
use function microtime;
use function str_starts_with;
use function number_format;
use function gettype;
use function is_array;

abstract class PDOConnection extends Connection
{
    /**
     * 连接配置
     * @var array
     */
    protected array $config = [
        'host' => '127.0.0.1',
        'port' => 3306,
        'username' => '',
        'password' => '',
        'database' => '',
        'charset' => 'utf8mb4',
        'dsn' => '',
        'socket' => '',
        'options' => [],
        'prefix' => '',
    ];

    /**
     * @var PDO
     */
    protected PDO $pdo;

    /**
     * PDO选项
     * @var array
     */
    protected array $options = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    /**
     * @var array
     */
    protected array $schemaInfo = [];

    /**
     * 执行语句
     * @param string $sql
     * @param array $bindings
     * @return ExecuteResultInterface
     * @throws Throwable
     */
    public function execute(string $sql, array $bindings = []): ExecuteResultInterface
    {
        $statement = $this->executeStatement($sql, $bindings);

        return new ExecuteResult(
            executeSql: $this->executeSql,
            executeBindings: $this->executeBindings,
            executeTime: $this->executeTime,
            rowCount: $statement->rowCount(),
            insertId: $this->isInsertSql($sql) ? $this->getLastInsertId() : null,
        );
    }

    /**
     * 查询结果集
     * @param string $sql
     * @param array $bindings
     * @return ExecuteResultInterface
     * @throws Throwable
     */
    public function query(string $sql, array $bindings = []): ExecuteResultInterface
    {
        $statement = $this->executeStatement($sql, $bindings);
        return new ExecuteResult(
            executeSql: $this->executeSql,
            executeBindings: $this->executeBindings,
            executeTime: $this->executeTime,
            resultSet: $statement->fetchAll(PDO::FETCH_ASSOC),
        );
    }

    /**
     * 执行预处理
     * @param string $sql
     * @param array $bindings
     * @return PDOStatement
     * @throws Throwable
     */
    public function executeStatement(string $sql, array $bindings = []): PDOStatement
    {
        try {
            $beginTime = microtime(true);
            $this->executeSql = $sql;
            $this->executeBindings = $bindings;
            $prepare = $this->pdo->prepare($sql);

            if (!empty($bindings)) {
                $prepare = $this->bindValue($prepare, $bindings);
            }

            $this->eventDispatcher->dispatch(new StatementPrepared($this, $prepare));

            $prepare->execute();

            $this->executeTime = (float)number_format((microtime(true) - $beginTime), 6);

            $this->eventDispatcher->dispatch(new QueryExecuted($this, $this->executeSql, $this->executeBindings, $this->executeTime));

            return $prepare;
        } catch (\PDOException $e) {
            throw new PDOException($e, $this->config, $this->buildSql($sql, $bindings));
        }
    }

    /**
     * 绑定参数
     * @param PDOStatement $statement
     * @param array $bindings
     * @return PDOStatement
     */
    public function bindValue(PDOStatement $statement, array $bindings = []): PDOStatement
    {
        foreach ($bindings as $key => $value) {
            $param = is_numeric($key) ? $key + 1 : ':' . $key;
            $type = $this->getBindType($value);
            $result = $statement->bindValue($param, $value, $type);
            if (!$result) {
                throw new BindParamException(sprintf(
                    'Error occurred when binding parameters type: %d,param:%s,value:%s', $type, $param, $value
                ), $this->config, $this->executeSql, $bindings);
            }
        }

        return $statement;
    }

    /**
     * 构建SQL语句
     * @param string $sql
     * @param array $bindings
     * @return string
     */
    public function buildSql(string $sql, array $bindings = []): string
    {
        foreach ($bindings as $key => $value) {
            $type = $this->getBindType($value);
            if ($type === PDO::PARAM_STR) {
                $value = '\'' . addcslashes((string)$value, "'") . '\'';
            }

            if (!is_array($value)) {
                $value = (string)$value;
            }

            $sql = is_numeric($key) ?
                substr_replace($sql, $value, strpos($sql, '?'), 1) :
                substr_replace($sql, $value, strpos($sql, ':' . $key), strlen(':' . $key));
        }

        return trim($sql);
    }

    /**
     * 获取绑定类型
     * @param mixed $value
     * @return int
     */
    protected function getBindType(mixed $value): int
    {
        return match (gettype($value)) {
            'integer' => PDO::PARAM_INT,
            'boolean' => PDO::PARAM_BOOL,
            'NULL' => PDO::PARAM_NULL,
            default => PDO::PARAM_STR
        };
    }

    /**
     * 解析连接信息
     * @param array $config
     * @return string
     */
    abstract public function parseDsn(array $config): string;

    /**
     * 获取表列信息
     * @param string $table
     * @return array
     */
    abstract public function getTableColumnInfo(string $table): array;

    /**
     * 获取数据表信息
     * @param string $table
     * @param bool $force
     * @return array
     */
    public function getSchemaInfo(string $table, bool $force = false): array
    {
        if (str_contains($table, '.')) {
            $schema = $table;
        } else {
            $schema = $this->getConfig('database') . '.' . $table;
        }

        if (!isset($this->schemaInfo[$schema]) || $force) {
            $cacheKey = $this->getSchemaCacheKey($schema);
            if (!isset(Schema::$cache[$cacheKey])) {
                Schema::$cache[$cacheKey] = $this->getTableColumnInfo($table);
            }
            $columnInfo = Schema::$cache[$cacheKey];
            $type = [];
            $primaryKey = [];
            $autoIncr = [];
            foreach ($columnInfo as $field => $info) {
                $type[$field] = Schema::getFieldType($info['type']);
                if ($info['primary_key']) {
                    $primaryKey[] = $field;
                }
                if ($info['auto_incr']) {
                    $autoIncr[] = $field;
                }
            }

            $this->schemaInfo[$schema] = [
                'fields' => array_keys($columnInfo),
                'type' => $type,
                'primaryKey' => $primaryKey,
                'autoIncr' => $autoIncr,
                'raw' => $columnInfo,
            ];
        }

        return $this->schemaInfo[$schema];
    }

    /**
     * 获取缓存key
     * @param string $schema
     * @return string
     */
    protected function getSchemaCacheKey(string $schema): string
    {
        return $this->getConfig('host') . ':' . $this->getConfig('port') . '@' . $schema;
    }

    /**
     * @param string|null $name
     * @return string|null
     */
    public function getLastInsertId(?string $name = null): ?string
    {
        $insertId = $this->pdo->lastInsertId($name);
        return $insertId === false ? null : $insertId;
    }

    /**
     * @param string $sql
     * @return bool
     */
    public function isInsertSql(string $sql): bool
    {
        $sql = strtoupper($sql);
        return str_starts_with($sql, 'INSERT') || str_starts_with($sql, 'REPLACE');
    }

    /**
     * 开启事务
     * @return void
     * @throws Throwable
     */
    public function beginTransaction(): void
    {
        throw_unless($this->pdo->beginTransaction(), TransactionException::class, 'Transaction begin failed.');
        $this->getEventDispatcher()->dispatch(new TransactionBeginning($this));
    }

    /**
     * 提交事务
     * @return void
     * @throws Throwable
     */
    public function commit(): void
    {
        throw_unless($this->pdo->commit(), TransactionException::class, 'Transaction commit failed.');
        $this->getEventDispatcher()->dispatch(new TransactionCommitted($this));
    }

    /**
     * 回滚事务
     * @return void
     * @throws Throwable
     */
    public function rollback(): void
    {
        throw_unless($this->pdo->rollBack(), TransactionException::class, 'Transaction rollback failed.');
        $this->getEventDispatcher()->dispatch(new TransactionRolledBack($this));
    }

    /**
     * 事务闭包
     * @param Closure $callback
     * @return mixed
     * @throws Throwable
     */
    public function transaction(Closure $callback): mixed
    {
        $this->beginTransaction();
        try {
            $result = $callback();
            $this->commit();
            return $result;
        } catch (Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * @return bool
     */
    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }

    /**
     * @return bool
     */
    public function connect(): bool
    {
        $this->pdo = new PDO($this->parseDsn($this->config), $this->config['username'], $this->config['password'], $this->getOptions());
        return true;
    }

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        return isset($this->pdo);
    }

    /**
     * @return bool
     */
    public function ping(): bool
    {
        try {
            $this->query('select 1');
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function reset(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        unset($this->pdo);
        return true;
    }

    /**
     * @return PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * @return array
     */
    protected function getOptions(): array
    {
        return $this->options + $this->config['options'];
    }
}