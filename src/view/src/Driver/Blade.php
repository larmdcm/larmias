<?php

declare(strict_types=1);

namespace Larmias\View\Driver;

use Larmias\View\Driver\Blade\Compilers\BladeCompiler;
use Larmias\View\Driver\Blade\Engines\CompilerEngine;
use Larmias\View\Driver\Blade\FileViewFinder;
use Larmias\View\Driver\Blade\Factory;
use function array_merge;

class Blade extends Driver
{
    /**
     * @var Factory
     */
    protected Factory $factory;

    /**
     * @var CompilerEngine
     */
    protected CompilerEngine $compilerEngine;

    /**
     * @var FileViewFinder
     */
    protected FileViewFinder $fileViewFinder;

    /**
     * @return void
     */
    public function initialize(): void
    {
        $compiler = new BladeCompiler($this->config['view_cache_path'], $this->config['tpl_cache']);
        $compiler->setContentTags($this->config['tpl_begin'], $this->config['tpl_end'], true);
        $compiler->setRawTags($this->config['tpl_raw_begin'], $this->config['tpl_raw_end']);
        $this->compilerEngine = new CompilerEngine($compiler);
        $this->fileViewFinder = new FileViewFinder($this->config['view_path'], $this->config['view_suffix']);
        $this->factory = $this->factory();
        if (!empty($this->config['namespace'])) {
            foreach ($this->config['namespace'] as $namespace => $hints) {
                $this->factory->addNamespace($namespace, $hints);
            }
        }
    }

    /**
     * @param string $location
     * @return void
     */
    public function addLocation(string $location): void
    {
        $this->factory->addLocation($location);
    }

    /**
     * @param string $namespace
     * @param array|string $hints
     * @return void
     */
    public function addNamespace(string $namespace, array|string $hints): void
    {
        $this->factory->addNamespace($namespace, $hints);
    }

    /**
     * @param string $path
     * @param array $vars
     * @return string
     */
    public function render(string $path, array $vars = []): string
    {
        $tpl = $this->factory->make($path, array_merge($this->vars, $vars))->render();
        $this->vars = [];
        return $tpl;
    }

    /**
     * @return Factory
     */
    public function factory(): Factory
    {
        return $this->factory = new Factory($this->compilerEngine, $this->fileViewFinder);
    }
}