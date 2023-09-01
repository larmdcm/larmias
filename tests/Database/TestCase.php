<?php

declare(strict_types=1);

namespace LarmiasTest\Database;

use Larmias\Database\Contracts\ConnectionInterface;
use Larmias\Database\Contracts\ManagerInterface;
use Larmias\Database\Exceptions\PDOException;
use Larmias\Database\Manager;
use Larmias\Database\Model\Model;
use Larmias\Database\Query\Builder\Builder;
use Larmias\Database\Query\Contracts\QueryInterface;
use Larmias\Event\EventDispatcherFactory;
use Larmias\Event\ListenerProviderFactory;
use Larmias\Facade\AbstractFacade;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\EventDispatcher\ListenerProviderInterface;
use function LarmiasTest\container;
use function Larmias\Utils\format_exception;

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
        $container = container();

        $container->bind(ListenerProviderInterface::class, ListenerProviderFactory::make($container, [
            DbQueryExecutedListener::class,
        ]));

        AbstractFacade::setContainer($container);

        $this->manager = new Manager($container, require __DIR__ . '/database.php');
        $this->manager->setEventDispatcher(EventDispatcherFactory::make($container));

        $container->bind(ManagerInterface::class, $this->manager);

        Model::maker(function (Model $model) {
            $model->setManager($this->manager);
        });
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
            \Larmias\Utils\println($e->getSql());
            \Larmias\Utils\println(format_exception($e));
        }
    }
}