<?php

declare(strict_types=1);

namespace Larmias\View\Drivers;

use Larmias\View\Blade\Compilers\BladeCompiler;
use Larmias\View\Blade\Engines\CompilerEngine;
use Larmias\View\Blade\FileViewFinder;
use Larmias\View\Blade\Factory;

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
        $compiler->setContentTags($this->config['tpl_begin'], $this->config['tpl_end'], false);
        $compiler->setRawTags($this->config['tpl_raw_begin'], $this->config['tpl_raw_end']);
        $this->compilerEngine = new CompilerEngine($compiler);
        $this->fileViewFinder = new FileViewFinder([$this->config['view_path']], [$this->config['view_suffix'], 'tpl']);

        $this->factory = $this->factory();
    }

    /**
     * @param string $path
     * @param array $vars
     * @return string
     */
    public function render(string $path, array $vars = []): string
    {
        $tpl = $this->factory->file($this->parsePath($path), \array_merge($this->vars, $vars))->render();
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