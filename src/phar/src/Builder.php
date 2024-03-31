<?php

declare(strict_types=1);

namespace Larmias\Phar;

use Larmias\Stringable\Str;
use Larmias\Support\FileSystem;
use Phar;
use RuntimeException;

class Builder
{
    /**
     * @var array
     */
    protected array $config = [];

    /**
     * @var FileSystem
     */
    protected FileSystem $fileSystem;

    /**
     * @var Phar
     */
    protected Phar $phar;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge(require dirname(__DIR__) . '/publish/phar.php', $config);
        $this->fileSystem = new FileSystem();
    }

    /**
     * @return void
     */
    public function build(): void
    {
        $this->checkEnv();

        if (empty($this->config['build_dir'])) {
            throw new RuntimeException('config not set build dir.');
        }

        $pharFile = Str::template($this->config['phar_file'], $this->config);
        $pharBaseDir = dirname($pharFile);
        if (!$this->fileSystem->isDirectory($pharBaseDir)) {
            $this->fileSystem->makeDirectory($pharBaseDir, 0755, true, true);
        }

        if ($this->fileSystem->isFile($pharFile)) {
            $this->fileSystem->delete($pharFile);
        }

        $this->phar = new Phar($pharFile, 0, $this->config['phar_alias']);
        $this->phar->startBuffering();
        $this->phar->buildFromDirectory($this->config['build_dir'], $this->config['exclude_pattern']);

        $excludeFiles = $this->config['exclude_files'];
        foreach ($excludeFiles as $file) {
            if ($this->phar->offsetExists($file)) {
                $this->phar->delete($file);
            }
        }

        $this->phar->setStub($this->getPharStub());
        $this->phar->stopBuffering();
    }

    /**
     * @return void
     */
    public function checkEnv(): void
    {
        if (!class_exists(Phar::class, false)) {
            throw new RuntimeException("The 'phar' extension is required for build phar package");
        }

        if (ini_get('phar.readonly')) {
            throw new RuntimeException(
                "The 'phar.readonly' is 'On', build phar must setting it 'Off'"
            );
        }
    }

    /**
     * @return string
     */
    protected function getPharStub(): string
    {
        return sprintf("#!/usr/bin/env php 
<?php
define('LARMIAS_IN_PHAR', true);
Phar::mapPhar('%s');
require 'phar://%s/%s';
__HALT_COMPILER();
", $this->config['phar_alias'], $this->config['phar_alias'], $this->config['main_file']);
    }
}