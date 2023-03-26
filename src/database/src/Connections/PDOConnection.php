<?php

declare(strict_types=1);

namespace Larmias\Database\Connections;

use Larmias\Database\Contracts\ExecuteResultInterface;
use Larmias\Database\Entity\ExecuteResult;
use Larmias\Database\Exceptions\BindParamException;
use Larmias\Database\Exceptions\PDOException;
use PDO;
use PDOStatement;
use Throwable;
use function is_numeric;
use function is_array;
use function addcslashes;
use function substr_replace;
use function strpos;
use function strlen;
use function microtime;
use function round;
use function str_starts_with;

abstract class PDOConnection extends Connection
{
    /**
     * @var int
     */
    public const PARAM_FLOAT = 21;

    /**
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
     * @param string $sql
     * @param array $binds
     * @return ExecuteResultInterface
     * @throws Throwable
     */
    public function execute(string $sql, array $binds = []): ExecuteResultInterface
    {
        $statement = $this->executeStatement($sql, $binds);
        return new ExecuteResult(
            executeSql: $this->executeSql,
            executeTime: $this->executeTime,
            rowCount: $statement->rowCount(),
            insertId: $this->isInsertSql($sql) ? $this->getLastInsertId() : null,
        );
    }

    /**
     * @param string $sql
     * @param array $binds
     * @return ExecuteResultInterface
     * @throws Throwable
     */
    public function query(string $sql, array $binds = []): ExecuteResultInterface
    {
        $statement = $this->executeStatement($sql, $binds);
        return new ExecuteResult(
            executeSql: $this->executeSql,
            executeTime: $this->executeTime,
            resultSet: $statement->fetchAll(PDO::FETCH_ASSOC),
        );
    }

    /**
     * 执行预处理
     * @param string $sql
     * @param array $binds
     * @return PDOStatement
     * @throws Throwable
     */
    public function executeStatement(string $sql, array $binds = []): PDOStatement
    {
        try {
            $beginTime = microtime(true);
            $this->executeSql = $sql;
            $this->lastBinds = $binds;
            $prepare = $this->pdo->prepare($sql);
            if (!empty($binds)) {
                $prepare = $this->bindValue($prepare, $binds);
            }

            $prepare->execute();

            $this->executeTime = round((microtime(true) - $beginTime) * 1000, 2);

            return $prepare;
        } catch (\PDOException $e) {
            throw new PDOException($e);
        }
    }

    /**
     * 绑定参数
     * @param PDOStatement $statement
     * @param array $binds
     * @return PDOStatement
     */
    public function bindValue(PDOStatement $statement, array $binds = []): PDOStatement
    {
        foreach ($binds as $key => $value) {
            $param = is_numeric($key) ? $key + 1 : ':' . $key;
            $type = PDO::PARAM_STR;
            if (is_array($value)) {
                $type = $value[1] ?? PDO::PARAM_STR;
                $value = $value[0] ?? '';
                if ($type === self::PARAM_FLOAT) {
                    $type = PDO::PARAM_STR;
                    $value = (float)$value;
                }
            }
            $result = $statement->bindValue($param, $value, $type);
            if (!$result) {
                throw new BindParamException(sprintf(
                    'Error occurred  when binding parameters type: %d,param:%s,value:%s', $type, $param, $value
                ), $this->config, $this->executeSql, $binds);
            }
        }

        return $statement;
    }

    /**
     * @param string $sql
     * @param array $binds
     * @return string
     */
    public function buildSql(string $sql, array $binds = []): string
    {
        foreach ($binds as $key => $value) {
            $type = PDO::PARAM_STR;
            if (is_array($value)) {
                $type = $value[1] ?? PDO::PARAM_STR;
                $value = $value[0] ?? '';
            }
            if ($type === self::PARAM_FLOAT || $type === PDO::PARAM_STR) {
                $value = '\'' . addcslashes((string)$value, "'") . '\'';
            }
            $sql = is_numeric($key) ?
                substr_replace($sql, $value, strpos($sql, '?'), 1) :
                substr_replace($sql, $value, strpos($sql, ':' . $key), strlen(':' . $key));
        }

        return trim($sql);
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
     * @param array $config
     * @return array
     */
    public function getOptions(array $config): array
    {
        return $this->options + $config['options'];
    }

    /**
     * @return bool
     */
    public function connect(): bool
    {
        $this->pdo = new PDO($this->parseDsn($this->config), $this->config['username'], $this->config['password'], $this->getOptions($this->config));
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
    public function reset(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        $this->pdo = null;
        return true;
    }
}