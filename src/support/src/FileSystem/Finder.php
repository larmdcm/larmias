<?php

declare(strict_types=1);

namespace Larmias\Support\FileSystem;

use FilesystemIterator;
use Larmias\Collection\Arr;
use Larmias\Stringable\Str;
use RuntimeException;
use SplFileInfo;
use function array_merge;
use function array_unique;
use function glob;
use function implode;
use function in_array;
use function is_array;
use function preg_match;
use function preg_quote;
use function str_contains;

/**
 * @method Finder include(array|string $path)
 * @method Finder exclude(array|string $path)
 * @method Finder includeFile(array|string $file)
 * @method Finder excludeFile(array|string $file)
 * @method Finder includeExt(array|string $ext)
 * @method Finder excludeExt(array|string $ext)
 * @method Finder depth(int $depth)
 */
class Finder
{
    /**
     * @var array[]
     */
    protected array $options = [
        'include' => [],
        'exclude' => [],
        'include_file' => [],
        'exclude_file' => [],
        'include_ext' => [],
        'exclude_ext' => [],
        'depth' => 0,
    ];

    /**
     * @return static
     */
    public static function create(): static
    {
        return new static();
    }

    /**
     * @return SplFileInfo[]
     */
    public function files(): array
    {
        $files = [];
        foreach ($this->options['include'] as $path) {
            $files = array_merge($files, $this->findFiles($path));
        }
        return $files;
    }

    /**
     * @param string $path
     * @param int $depth
     * @return SplFileInfo[]
     */
    protected function findFiles(string $path, int $depth = 0): array
    {
        $files = [];
        if (str_contains($path, '*')) {
            $iterator = glob($path);
        } else {
            $iterator = new FilesystemIterator($path);
        }

        foreach ($iterator as $info) {
            if (!($info instanceof SplFileInfo)) {
                $info = new SplFileInfo($info);
            }

            if ($this->options['depth'] > 0 && $depth >= $this->options['depth']) {
                break;
            }

            /** @var SplFileInfo $info */
            if ($info->isDir() && !$info->isLink()) {
                if ($this->checkDir($info)) {
                    $files = array_merge($this->findFiles($info->getPathname(), ++$depth), $files);
                }
            } else {
                if ($this->checkFile($info)) {
                    $files[] = $info;
                }
            }
        }
        return $files;
    }

    /**
     * @param SplFileInfo $info
     * @return bool
     */
    protected function checkDir(SplFileInfo $info): bool
    {
        if (!empty($this->options['exclude'])) {
            if (!isset($this->options['excludedDirs'])) {
                $patterns = [];
                $excludedDirs = [];
                foreach ($this->options['exclude'] as $directory) {
                    if (str_contains($directory, '/')) {
                        $patterns[] = preg_quote($directory, '#');
                    } else {
                        $excludedDirs[$directory] = true;
                    }
                }
                if ($patterns) {
                    $this->options['excludedPattern'] = '#(?:^|/)(?:' . implode('|', $patterns) . ')(?:/|$)#';
                }
                $this->options['excludedDirs'] = $excludedDirs;
            }
            if (isset($this->options['excludedDirs'][$info->getFilename()])) {
                return false;
            }
            if (isset($this->options['excludedPattern'])) {
                $path = str_replace("\\", "/", $info->getPath());
                return !preg_match($this->options['excludedPattern'], $path);
            }
        }
        return true;
    }

    /**
     * @param SplFileInfo $info
     * @return bool
     */
    protected function checkFile(SplFileInfo $info): bool
    {
        $name = $info->getFilename();
        $ext = $info->getExtension();

        if (!empty($this->options['include_ext'])) {
            if (!in_array($ext, $this->options['include_ext'])) {
                return false;
            }
        }

        if (!empty($this->options['exclude_ext'])) {
            if (in_array($ext, $this->options['exclude_ext'])) {
                return false;
            }
        }

        if (!empty($this->options['include_file'])) {
            if (!in_array($name, $this->options['include_file'])) {
                return false;
            }
        }

        if (!empty($this->options['exclude_file'])) {
            if (in_array($name, $this->options['exclude_file'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return self
     */
    protected function setOptions(string $name, mixed $value): self
    {
        if (is_array($this->options[$name])) {
            $this->options[$name] = array_unique(array_merge($this->options[$name], Arr::wrap($value)));
        } else {
            $this->options[$name] = $value;
        }
        return $this;
    }

    /**
     * Finder __call.
     * @param string $name
     * @param array $arguments
     * @return self
     */
    public function __call(string $name, array $arguments)
    {
        $optName = Str::snake($name);
        if (isset($this->options[$optName])) {
            return $this->setOptions($optName, $arguments[0]);
        }
        throw new RuntimeException(__CLASS__ . '->' . $name . ' method not exists');
    }
}