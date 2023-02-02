<?php

declare(strict_types=1);

namespace Larmias\Snowflake;

use Larmias\Snowflake\Contracts\IdGeneratorInterface;

class IdGenerator implements IdGeneratorInterface
{
    /**
     * @var array|null[]
     */
    protected array $config = [
        'datacenter_id' => null,
        'worker_id' => null,
        'start_millisecond' => null,
        'sequence' => null,
    ];

    /**
     * @var int|null
     */
    protected ?int $workerId;

    /**
     * @var int|null
     */
    protected ?int $datacenterId;

    /**
     * IdGenerator constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = \array_merge($this->config, $config);
        $this->datacenterId = $this->config['datacenter_id'] ?: 0;
        $this->workerId = $this->config['worker_id'] ?: 0;
    }

    public function id(): string
    {
        return '';
    }

    public function getWorkerId(): int
    {
        return $this->workerId;
    }

    /**
     * @return int
     */
    public function getDatacenter(): int
    {
        return $this->datacenterId;
    }
}