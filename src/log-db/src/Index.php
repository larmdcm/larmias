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
     * @var int
     */
    protected int $indexInterval = 1000;

    /**
     * @param string $indexFile
     */
    public function __construct(string $indexFile)
    {
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
            $lines = explode(',', $this->file->fgets());
            $this->update($lines[0], $lines[1]);
        }
    }

    /**
     * 更新索引并追加到文件中
     * @param string $key
     * @param string $offset
     * @return void
     */
    public function append(string $key, string $offset): void
    {
        $this->indexCount++;
        if ($this->indexCount % $this->indexInterval != 0) {
            return;
        }
        $this->update($key, $offset);
        $this->file->fwrite(sprintf('%s,%s%s', $key, $offset, PHP_EOL));
    }

    /**
     * 获取索引值偏移量
     * @param string $beginKey
     * @param string $endKey
     * @return array
     */
    public function offset(string $beginKey, string $endKey): array
    {
        $left = (int)array_search($beginKey, $this->indexes);
        $right = (int)array_search($endKey, $this->indexes);
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

        return [$this->offset[$left], $this->offset[$right]];
    }

    /**
     * 更新索引
     * @param string $key
     * @param string $offset
     * @return void
     */
    protected function update(string $key, string $offset): void
    {
        $this->indexes[] = $key;
        $this->offset[] = $offset;
    }
}