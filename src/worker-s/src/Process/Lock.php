<?php

declare(strict_types=1);

namespace Larmias\WorkerS\Process;

use Larmias\WorkerS\Support\Helper;
use Throwable;

class Lock
{
    /**
     * @var string
     */
    protected string $filename;

    /**
     * @var string
     */
    const SUFFIX = '.lock';

    /**
     * @param string $name
     * @param string|null $path
     */
    public function __construct(string $name = 'lock',?string $path = null)
    {
        $path = $path ?: Helper::getLockPath();
        $this->filename = Helper::getFilePath($path . ltrim($name,'/') . self::SUFFIX);
    }

    /**
     * @param string $name
     * @param string|null $path
     * @return Lock
     */
    public static function create(string $name = 'lock',?string $path = null): Lock
    {
        return new static($name,$path);
    }

    /**
     * @param callable $func
     * @param boolean  $block
     * @return mixed
     */
    public function try(callable $resolve,bool $block = true)
    {
        $result = null;
        $fd = \fopen($this->filename,'r');
        if (!\is_resource($fd)) {
            return null;
        }
        try {
            $flag    = $block ? \LOCK_EX : \LOCK_EX | \LOCK_NB;
            $getLock = \flock($fd,$flag);
            if ($getLock) {
                $result = \call_user_func($resolve);
            }
        } catch (Throwable $e) {
            throw $e; 
        } finally {
            \flock($fd,\LOCK_UN);
            \fclose($fd);
        }
        return $result;
    }
}