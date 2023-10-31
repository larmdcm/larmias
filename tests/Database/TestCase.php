<?php

declare(strict_types=1);

namespace LarmiasTest\Database;

use Larmias\Context\ApplicationContext;
use Larmias\Database\Contracts\ConnectionInterface;
use Larmias\Database\Contracts\ManagerInterface;
use Larmias\Database\Exceptions\PDOException;
use Larmias\Database\Manager;
use Larmias\Database\Query\Builder\Builder;
use Larmias\Database\Query\Contracts\QueryInterface;
use Larmias\Event\ListenerProviderFactory;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\EventDispatcher\ListenerProviderInterface;
use function Larmias\Support\format_exception;

class TestCase extends BaseTestCase
{
    /**
     * @var Manager
     */
    protected Manager $manager;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $container = ApplicationContext::getContainer();
        ListenerProviderFactory::register($container->get(ListenerProviderInterface::class), $container, DbQueryExecutedListener::class);
        $this->manager = $container->get(ManagerInterface::class);
    }

    /**
     * @return ConnectionInterface
     */
    protected function getConnection(): ConnectionInterface
    {
        return $this->manager->connection();
    }

    /**
     * @return QueryInterface
     */
    protected function newQuery(): QueryInterface
    {
        return $this->manager->newQuery($this->manager->connection());
    }

    /**
     * @return Builder
     */
    public function newBuilder(): Builder
    {
        return $this->manager->newBuilder($this->manager->connection());
    }

    /**
     * @param \Closure $callback
     * @return void
     */
    protected function handleException(\Closure $callback): void
    {
        try {
            $callback();
        } catch (PDOException $e) {
            \Larmias\Support\println($e->getSql());
            \Larmias\Support\println(format_exception($e));
        }
    }
}