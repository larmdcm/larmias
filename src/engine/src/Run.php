<?php

declare(strict_types=1);

namespace Larmias\Engine;

use Larmias\Contracts\ContainerInterface;
use Larmias\Engine\Contracts\KernelInterface;
use function is_array;
use function array_merge;

class Run
{
    /**
     * @var array
     */
    protected array $config = [
        'driver' => null
    ];

    /**
     * @param ContainerInterface $container
     * @param KernelInterface|null $kernel
     */
    public function __construct(protected ContainerInterface $container, protected ?KernelInterface $kernel = null)
    {
        $this->kernel ??= new Kernel($this->container);
    }

    /**
     * @param string|array $name
     * @param mixed|null $value
     * @return self
     */
    public function set(string|array $name, mixed $value = null): self
    {
        if (is_array($name)) {
            $this->config = array_merge($this->config, $name);
        } else {
            $this->config[$name] = $value;
        }
        return $this;
    }

    /**
     * @param ...$args
     * @return void
     */
    public function __invoke(...$args): void
    {
        $this->kernel->setConfig(EngineConfig::build([
            'driver' => $this->config['driver'],
            'settings' => $this->config['settings'] ?? [],
        ]));

        $this->kernel->addWorker(WorkerConfig::build([
            'name' => $this->config['main_process_name'] ?? 'MainProcess',
            'type' => WorkerType::WORKER_PROCESS,
            'callbacks' => [
                Event::ON_WORKER_START => function ($worker) use ($args) {
                    $this->container->invoke($args[0], [$worker, $this->kernel]);
                }
            ]
        ]));

        $this->kernel->run();
    }
}