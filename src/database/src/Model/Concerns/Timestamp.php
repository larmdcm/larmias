<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Concerns;

use Larmias\Database\Model\Model;
use RuntimeException;
use function date;
use function time;

/**
 * @mixin Model
 */
trait Timestamp
{
    /**
     * 自动时间写入
     * @var bool
     */
    protected bool $autoWriteTimestamp = true;

    /**
     * 创建时间字段
     * @var string|null
     */
    protected ?string $createTimeField = 'create_time';

    /**
     * 修改时间字段
     * @var string|null
     */
    protected ?string $updateTimeField = 'update_time';

    /**
     * 时间写入类型
     * @var string
     */
    protected string $autoWriteTimestampFieldType = 'datetime';

    /**
     * 获取写入时间
     * @return string|int
     */
    public function getTimestamp(): string|int
    {
        $nowTime = time();
        return match ($this->autoWriteTimestampFieldType) {
            'datetime' => date('Y-m-d H:i:s.u', $nowTime),
            'date' => date('Y-m-d', $nowTime),
            'integer' => $nowTime,
            default => throw new RuntimeException("getTimestamp autoWriteTimestampFieldType parse error"),
        };
    }

    /**
     * 检查时间写入
     * @param array $data
     * @param bool $isUpdate
     * @return bool
     */
    public function checkTimeStampWrite(array &$data, bool $isUpdate = false): bool
    {
        if (!$this->autoWriteTimestamp) {
            return false;
        }

        if ($this->createTimeField && !$isUpdate) {
            $data[$this->createTimeField] = $this->getTimestamp();
            $this->data[$this->createTimeField] = $data[$this->createTimeField];
        }

        if ($this->updateTimeField && $isUpdate) {
            $data[$this->updateTimeField] = $this->getTimestamp();
            $this->data[$this->updateTimeField] = $data[$this->updateTimeField];
        }

        return true;
    }

    /**
     * @return string|null
     */
    public function getCreateTimeField(): ?string
    {
        return $this->createTimeField;
    }

    /**
     * @return string|null
     */
    public function getUpdateTimeField(): ?string
    {
        return $this->updateTimeField;
    }
}