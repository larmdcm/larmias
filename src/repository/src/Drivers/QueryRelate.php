<?php

declare(strict_types=1);

namespace Larmias\Repository\Drivers;

use Larmias\Repository\Contracts\QueryRelateInterface;

abstract class QueryRelate implements QueryRelateInterface
{
    /**
     * @var object
     */
    protected object $query;

    /**
     * @return object
     */
    public function getQuery(): object
    {
        return $this->query;
    }

    /**
     * @param object $query
     * @return self
     */
    public function setQuery(object $query): self
    {
        $this->query = $query;
        return $this;
    }

    /**
     * QueryRelate __call.
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        $result = \call_user_func_array([$this->query, $name], $arguments);
        if ($result instanceof $this->query) {
            $this->query = $result;
            return $this;
        }
        return $result;
    }
}