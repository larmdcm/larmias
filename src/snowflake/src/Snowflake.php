<?php

declare(strict_types=1);

namespace Larmias\Snowflake;

use Godruoyi\Snowflake\Snowflake as BaseSnowflake;

class Snowflake extends BaseSnowflake
{
    /**
     * @param int $workerId
     * @return void
     */
    public function setWorkerId(int $workerId): void
    {
        $this->workerid = $workerId;
    }

    /**
     * @param int $datacenterId
     * @return void
     */
    public function setDatacenterId(int $datacenterId): void
    {
        $this->datacenter = $datacenterId;
    }
}