<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Concerns;

use Larmias\Database\Model\AbstractModel;
use RuntimeException;
use function date;
use function time;

/**
 * @mixin AbstractModel
 */
trait Timestamp
{
    /**
     * @var bool
     */
    protected bool $autoWriteTimestamp = true;

    /**
     * @var string|null
     */
    protected ?string $createTime = 'create_time';

    /**
     * @var string|null
     */
    protected ?string $updateTime = 'update_time';

    /**
     * @var string
     */
    protected string $autoWriteTimestampFieldType = 'datetime';

    /**
     * @return string|int
     */
    protected function getTimestamp(): string|int
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
     * @return bool
     */
    protected function checkTimeStampWrite(): bool
    {
        if (!$this->autoWriteTimestamp) {
            return false;
        }

        if ($this->createTime && !$this->isExists()) {
            $this->data[$this->createTime] = $this->getTimestamp();
        }

        if ($this->updateTime && $this->isExists()) {
            $this->data[$this->updateTime] = $this->getTimestamp();
        }

        return true;
    }
}