<?php

declare(strict_types=1);

namespace Larmias\Contracts;

use Closure;

interface ServiceDiscoverInterface
{
    /**
     * @var string
     */
    public const SERVICE_PROVIDER = 'providers';

    /**
     * @var string
     */
    public const SERVICE_COMMAND = 'commands';

    /**
     * @var string
     */
    public const SERVICE_PROCESS = 'process';

    /**
     * @var string
     */
    public const SERVICE_SERVER = 'server';

    /**
     * @var string
     */
    public const SERVICE_LISTENER = 'listeners';

    /**
     * 发现服务
     * @param Closure $callback
     * @return void
     */
    public function discover(Closure $callback): void;

    /**
     * 注册服务
     * @param string $name
     * @param string $class
     * @param array $args
     * @return void
     */
    public function register(string $name, string $class, array $args = []): void;

    /**
     * 获取注册的服务
     * @return array
     */
    public function services(): array;

    /**
     * 添加服务提供者
     * @param string|array $providers
     * @return void
     */
    public function providers(string|array $providers): void;

    /**
     * 添加命令服务
     * @param string|array $commands
     * @return void
     */
    public function commands(string|array $commands): void;

    /**
     * 添加进程服务
     * @param string $process
     * @param string|null $name
     * @param int|null $num
     * @param array $options
     * @return void
     */
    public function addProcess(string $process, ?string $name = null, ?int $num = 1, array $options = []): void;

    /**
     * 添加事件监听服务
     * @param string|array $listeners
     * @return void
     */
    public function listener(string|array $listeners): void;
}