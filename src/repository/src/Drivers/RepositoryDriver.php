<?php

declare(strict_types=1);

namespace Larmias\Repository\Drivers;

use Larmias\Repository\Contracts\RepositoryDriverInterface;
use Larmias\Repository\AbstractRepository;

abstract class RepositoryDriver implements RepositoryDriverInterface
{
    protected AbstractRepository $repository;

    protected QueryRelate $queryRelate;

    /**
     * @param AbstractRepository $repository
     */
    public function __construct(AbstractRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return AbstractRepository
     */
    public function getRepository(): AbstractRepository
    {
        return $this->repository;
    }

    /**
     * @param AbstractRepository $repository
     * @return self
     */
    public function setRepository(AbstractRepository $repository): self
    {
        $this->repository = $repository;
        return $this;
    }

    /**
     * @return QueryRelate
     */
    public function getQueryRelate(): QueryRelate
    {
        return $this->queryRelate;
    }

    /**
     * @param QueryRelate $queryRelate
     * @return self
     */
    public function setQueryRelate(QueryRelate $queryRelate): self
    {
        $this->queryRelate = $queryRelate;
        return $this;
    }
}