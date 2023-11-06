<?php

declare(strict_types=1);

namespace Larmias\View;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContextInterface;
use Larmias\Contracts\ViewInterface;
use function str_contains;
use function lcfirst;
use function substr;
use function strlen;
use function is_array;

class View implements ViewInterface
{
    /**
     * @var array
     */
    protected array $config = [
        'view_path' => [],
        'view_cache_path' => '',
        'namespace' => [],
        'tpl_cache' => true,
        'tpl_begin' => '{{',
        'tpl_end' => '}}',
        'tpl_raw_begin' => '{!!',
        'tpl_raw_end' => '!!}',
        'view_suffix' => ['php'],
    ];

    /**
     * @param ContainerInterface $container
     * @param ContextInterface $context
     * @param ConfigInterface $config
     */
    public function __construct(protected ContainerInterface $container, protected ContextInterface $context, ConfigInterface $config)
    {
        $this->config = array_merge($this->config, $config->get('view', []));
        if (!is_array($this->config['view_path'])) {
            $this->config['view_path'] = (array)$this->config['view_path'];
        }

        if (!is_array($this->config['view_suffix'])) {
            $this->config['view_suffix'] = (array)$this->config['view_suffix'];
        }
    }

    /**
     * @param string $location
     * @return void
     */
    public function addLocation(string $location): void
    {
        $this->config['view_path'][] = $location;
    }

    /**
     * @param string $namespace
     * @param array|string $hints
     * @return void
     */
    public function addNamespace(string $namespace, array|string $hints): void
    {
        $this->config['namespace'][$namespace] = $hints;
    }

    /**
     * @param string|array $name
     * @param mixed|null $value
     * @return ViewInterface
     */
    public function with(array|string $name, mixed $value = null): ViewInterface
    {
        return $this->driver()->with($name, $value);
    }

    /**
     * @param string $path
     * @param array $vars
     * @return string
     */
    public function render(string $path, array $vars = []): string
    {
        return $this->driver()->render($path, $vars);
    }

    /**
     * @return ViewInterface
     */
    public function driver(): ViewInterface
    {
        $config = $this->config;
        return $this->context->remember($config['driver'], fn() => $this->container->make($config['driver'], ['config' => $config], true));
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (str_contains($name, 'with')) {
            return $this->driver()->with(lcfirst(substr($name, -(strlen($name) - 4))), $arguments[0]);
        }

        return $this->driver()->{$name}(...$arguments);
    }
}