<?php

declare(strict_types=1);

namespace Larmias\WorkerS\Task\Channels;

use Larmias\WorkerS\Task\Channel;
use Larmias\WorkerS\Support\Helper;
use Larmias\WorkerS\Process\Lock;
use Larmias\WorkerS\Protocols\Frame;

class File extends Channel
{
    /**
     * @var string
     */
    const SUFFIX = '.ch';

    /**
     * @var Lock
     */
    protected Lock $lock;

    /**
     * @var string
     */
    protected string $filename;

    /**
     * @var array
     */
    protected array $config = [
        'path' => null,
    ];

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = \array_merge($this->config,$config);
    }

    /**
     * 初始化
     *
     * @return void
     */
    public function init(): void
    {
        $path           = $this->config['path'] ?: Helper::getChannelPath();
        $name           = $this->taskWorker->getKey();
        $this->lock     = Lock::create($name);
        $this->filename = Helper::getFilePath($path . ltrim($name,'/') . self::SUFFIX);
    }

    /**
     * @param  string  $raw
     * @return int|null
     */
    public function push(string $raw): ?int
    {
        $data = Frame::encode($raw,null);
        return $this->lock->try(function () use ($data) {
            $result = \file_put_contents($this->filename,$data,FILE_APPEND);
            if ($result === false) {
                return null;
            }
            return $result;
        });
    }

    /**
     * @return string|null
     */
    public function shift(): ?string
    {
        $dataShift = function () {
            $raw = \file_get_contents($this->filename);
            if (!$raw) {
                return null;
            }
            $total = Frame::input($raw,null);
            if (!$total) {
                return null;
            }
            $result = \substr($raw,0,$total);
            \file_put_contents($this->filename,\substr($raw,$total));
            return Frame::decode($result,null);
        };

        return $this->lock->try(fn() => $dataShift());
    }

    /**
     * @return boolean
     * @throws \Throwable
     */
    public function clear(): bool
    {
        return $this->lock->try(fn() => \file_put_contents($this->filename,'') !== false);
    }

    /**
     * @return boolean
     */
    public function close(): bool
    {
        return true;
    }
}