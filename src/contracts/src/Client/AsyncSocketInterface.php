<?php

declare(strict_types=1);

namespace Larmias\Contracts\Client;

interface AsyncSocketInterface extends BaseSocketInterface
{
    /**
     * @var string
     */
    public const ON_MESSAGE = 'message';

    /**
     * 监听事件
     * @param string|array $name
     * @param callable $callback
     * @return AsyncSocketInterface
     */
    public function on(string|array $name, callable $callback): AsyncSocketInterface;
}