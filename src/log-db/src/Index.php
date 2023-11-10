<?php

declare(strict_types=1);

namespace Larmias\LogDb;

use SplFileObject;

class Index
{
    /**
     * @var SplFileObject
     */
    protected SplFileObject $file;

    /**
     * @var array
     */
    protected array $indexes = [];

    /**
     * @var array
     */
    protected array $offset = [];

    /**
     * @var int
     */
    protected int $indexCount = 0;

    /**
     * @var array
     */
    protected array $config = [
        'index_interval' => 1000,
    ];

    /**
     * @param string $indexFile
     * @param array $config
     */
    public function __construct(string $indexFile, array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        $this->file = new SplFileObject($indexFile, 'a+');
    }

    /**
     * 加载索引到内存
     * @return void
     */
    public function load(): void
    {
        $this->file->seek(0);
        while (!$this->file->eof()) {
            $contents = $this->file->fgets();
            if (empty($contents)) {
                break;
            }
            $lines = explode(',', $contents);
            if (count($lines) < 2) {
                break;
            }
            $this->update((int)$lines[0], (int)$lines[1]);
        }
    }

    /**
     * 更新索引并追加到文件中
     * @param int $key
     * @param int $offset
     * @return void
     */
    public function update(int $key, int $offset): void
    {
        $this->indexCount++;
        if ($this->indexCount % $this->config['index_interval'] != 0) {
            return;
        }
        $this->append($key, $offset);
        $this->file->fwrite(sprintf('%s,%s%s', $key, $offset, PHP_EOL));
    }

    /**
     * 获取索引值偏移量
     * @param int $beginKey
     * @param int $endKey
     * @return int[]
     */
    public function offset(int $beginKey, int $endKey): array
    {
        $left = $this->bisect($this->indexes, $beginKey);
        $right = $this->bisect($this->indexes, $endKey);
        $left--;
        $right--;
        if ($left < 0) {
            $left = 0;
        }

        if ($right < 0) {
            $right = 0;
        }

        $count = count($this->indexes) - 1;
        if ($right > $count) {
            $right = $count;
        }

        return [$this->offset[$left] ?? 0, $this->offset[$right] ?? 0];
    }

    /**
     * 追加索引
     * @param int $key
     * @param int $offset
     * @return void
     */
    protected function append(int $key, int $offset): void
    {
        $this->indexes[] = $key;
        $this->offset[] = $offset;
    }

    /**
     * 返回确定值在数组中插入的位置
     * @param array $arr
     * @param int $val
     * @return int
     */
    protected function bisect(array $arr, int $val): int
    {
        $left = 0;
        $right = count($arr);

        while ($left < $right) {
            $mid = (int)floor(($left + $right) / 2);
            if ($arr[$mid] < $val) {
                $left = $mid + 1;
            } else {
                $right = $mid;
            }
        }

        return $left;
    }
}