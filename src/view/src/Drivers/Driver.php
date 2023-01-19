<?php

declare(strict_types=1);

namespace Larmias\View\Drivers;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ViewInterface;
use Larmias\View\Exceptions\TemplateNotFoundException;

abstract class Driver implements ViewInterface
{
    /**
     * @var array
     */
    protected array $config = [
        'view_path' => '',
        'tpl_cache' => true,
        'tpl_begin' => '{{',
        'tpl_end' => '}}',
        'tpl_raw_begin' => '{!!',
        'tpl_raw_end' => '!!}',
        'view_cache_path' => '',
        'view_suffix' => 'php',
    ];

    /**
     * @var array
     */
    protected array $vars = [];

    public function __construct(protected ContainerInterface $container, array $config = [])
    {
        $this->config = \array_merge($this->config, $config);

        if (\method_exists($this, 'initialize')) {
            $this->container->invoke([$this, 'initialize']);
        }
    }

    public function parsePath(string $path): string
    {
        $file = \rtrim($this->config['view_path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR .
            str_replace('.', DIRECTORY_SEPARATOR, $path) . '.' . $this->config['view_suffix'];
        if (!\is_file($file)) {
            throw new TemplateNotFoundException("Template not exists:" . $file, $file);
        }
        return $file;
    }

    /**
     * @param string|array $name
     * @param mixed|null $value
     * @return ViewInterface
     */
    public function with(string|array $name, mixed $value = null): ViewInterface
    {
        if (\is_array($name)) {
            foreach ($name as $k => $v) {
                $this->vars[$k] = $v;
            }
        } else {
            $this->vars[$name] = $value;
        }
        return $this;
    }
}