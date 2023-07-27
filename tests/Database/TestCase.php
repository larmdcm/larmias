<?php

declare(strict_types=1);

namespace Larmias\Tests\Database;

use Larmias\Database\Contracts\ConnectionInterface;
use Larmias\Database\Contracts\QueryInterface;
use Larmias\Database\Manager;
use Larmias\Database\Model\AbstractModel;
use Larmias\Database\Query\Builder\Builder;
use Larmias\Event\EventDispatcherFactory;
use Larmias\Event\ListenerProviderFactory;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\EventDispatcher\ListenerProviderInterface;
use function Larmias\Tests\container;

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

        $this->manager = new Manager($container, require __DIR__ . '/database.php');
        $this->manager->setEventDispatcher(EventDispatcherFactory::make($container));

        AbstractModel::maker(function (AbstractModel $model) {
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
}