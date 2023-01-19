<?php

declare(strict_types=1);

namespace Larmias\View\Drivers;

use Larmias\View\Blade\Compilers\BladeCompiler;
use Larmias\View\Blade\Engines\CompilerEngine;
use Larmias\View\Blade\FileViewFinder;
use Larmias\View\Blade\Factory;

class Blade extends Driver
{
    protected Factory $factory;

    public function initialize()
    {
        $compiler = new BladeCompiler($this->config['view_cache_path'], $this->config['tpl_cache']);
        $compiler->setContentTags($this->config['tpl_begin'], $this->config['tpl_end'], true);
        $compiler->setContentTags($this->config['tpl_begin'], $this->config['tpl_end'], false);
        $compiler->setRawTags($this->config['tpl_raw_begin'], $this->config['tpl_raw_end']);

        $engine = new CompilerEngine($compiler);
        $finder = new FileViewFinder([$this->config['view_path']], [$this->config['view_suffix'], 'tpl']);

        $this->factory = new Factory($engine, $finder);
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
}