<?php

declare(strict_types=1);

namespace Larmias\Database\Connections;

use Larmias\Database\Connections\Transaction\PDOTransaction;
use Larmias\Database\Contracts\ExecuteResultInterface;
use Larmias\Database\Contracts\TransactionInterface;
use Larmias\Database\Entity\ExecuteResult;
use Larmias\Database\Events\QueryExecuted;
use Larmias\Database\Exceptions\BindParamException;
use Larmias\Database\Exceptions\PDOException;
use PDO;
use PDOStatement;
use Throwable;
use Closure;
use function is_numeric;
use function addcslashes;
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
     * @var TransactionInterface|null
     */
    protected ?TransactionInterface $transaction = null;

    /**
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

            $prepare->execute();

            $this->executeTime = (float)number_format((microtime(true) - $beginTime), 6);

            $this->eventDispatcher->dispatch(new QueryExecuted($this->executeSql, $this->executeBindings, $this->executeTime));

            return $prepare;
        } catch (\PDOException $e) {
            throw new PDOException($e);
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
                    'Error occurred  when binding parameters type: %d,param:%s,value:%s', $type, $param, $value
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
     * @param array $config
     * @return string
     */
    abstract public function parseDsn(array $config): string;

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
        return str_starts_with($sql, 'INSERT') || str_starts_with($sql, 'REPLACE');
    }

    /**
     * 开启事务
     * @return TransactionInterface
     * @throws Throwable
     */
    public function beginTransaction(): TransactionInterface
    {
        if (!$this->transaction) {
            $this->transaction = new PDOTransaction($this);
        }
        return $this->transaction->beginTransaction();
    }

    /**
     * 事务闭包
     * @param Closure $callback
     * @return mixed
     * @throws Throwable
     */
    public function transaction(Closure $callback): mixed
    {
        $ctx = $this->beginTransaction();
        try {
            $result = $callback();
            $ctx->commit();
            return $result;
        } catch (Throwable $e) {
            $ctx->rollback();
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