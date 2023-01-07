<?php

declare(strict_types=1);

namespace Larmias\Repository;

use Larmias\Repository\Contracts\RepositoryDriverInterface;
use Larmias\Repository\Contracts\QueryRelateInterface;
use InvalidArgumentException;

class RepositoryFactory
{
    /**
     * @param RepositoryConfig $config
     * @param AbstractRepository $repository
     * @return RepositoryDriverInterface
     */
    public static function driver(RepositoryConfig $config, AbstractRepository $repository): RepositoryDriverInterface
    {
        $class = $config->get('driver');
        if (!$class) {
            throw new InvalidArgumentException("driver [{$class}] not found.");
        }
        return new $class($repository);
    }

    /**
     * @param RepositoryConfig $config
     * @return QueryRelateInterface
     */
    public static function query(RepositoryConfig $config): QueryRelateInterface
    {
        $class = $config->get('query');
        if (!$class) {
            throw new InvalidArgumentException("query [{$class}] not found.");
        }
        return new $class();
    }
}