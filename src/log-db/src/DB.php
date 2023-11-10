<?php

declare(strict_types=1);

namespace Larmias\LogDb;

use SplFileObject;
use Generator;
use Closure;

class DB
{
    /**
     * @var array
     */
    protected array $config = [
        'data_dir' => null,
        'write_buffer_size' => 10240,
        'read_buffer_size' => 10240,
        'index_interval' => 1000,
    ];

    /**
     * @var SplFileObject
     */
    protected SplFileObject $writer;

    /**
     * @var Index
     */
    protected Index $index;

    /**
     * @var string
     */
    protected string $dbFile;

    /**
     * @param string $name
     * @param array $config
     */
    public function __construct(protected string $name, array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        if (!$this->config['data_dir']) {
            $this->config['data_dir'] = sys_get_temp_dir();
        }
    }

    /**
     * @return void
     */
    public function load(): void
    {
        $this->dbFile = $this->config['data_dir'] . '/' . $this->name . '.db';
        $this->index = new Index($this->config['data_dir'] . '/' . $this->name . '.idx', $this->config);
        $baseDir = dirname($this->dbFile);
        is_dir($baseDir) || mkdir($baseDir, 0755, true);
        $this->writer = new SplFileObject($this->dbFile, 'a');
    }

    /**
     * @param string $contents
     * @param int|null $time
     * @return void
     */
    public function record(string $contents, ?int $time = null): void
    {
        $time = $time ?: time();
        $offset = $this->writer->ftell();
        $this->writer->fwrite(sprintf('%s,%s%s', $time, $contents, PHP_EOL));
        $this->index->update($time, $offset);
    }

    /**
     * @param array $timeInterval
     * @param Closure|null $filter
     * @return Generator
     */
    public function query(array $timeInterval, ?Closure $filter = null): Generator
    {
        [$beginTime, $endTime] = $timeInterval;
        $offsets = $this->index->offset($beginTime, $endTime);
        $data = $this->getDataByOffsets($timeInterval, $offsets);
        foreach ($data as $item) {
            $check = true;
            if ($filter) {
                $check = $filter($item);
            }
            if ($check) {
                yield $item;
            }
        }
    }

    /**
     * @param array $timeInterval
     * @param array $offsets
     * @return Generator
     */
    protected function getDataByOffsets(array $timeInterval, array $offsets): Generator
    {
        [$beginTime, $endTime] = $timeInterval;
        [$beginOffset, $endOffset] = $offsets;
        $reader = new SplFileObject($this->dbFile, 'r');
        $reader->seek($beginOffset);

        $line = $reader->fgets();
        $findRealBeginOffset = false;
        $willReadLen = $endOffset - $beginOffset;
        $readLen = 0;
        while (!empty($line)) {
            $readLen += strlen($line);
            if (!$findRealBeginOffset && $line < $beginTime) {
                $line = $reader->fgets();
                continue;
            }
            $findRealBeginOffset = true;
            if ($readLen >= $willReadLen && $line > $endTime) {
                break;
            }
            yield rtrim($line, "\r\n");
            $line = $reader->fgets();
        }
    }
}