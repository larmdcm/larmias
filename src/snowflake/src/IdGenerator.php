<?php

declare(strict_types=1);

namespace Larmias\Snowflake;

use Godruoyi\Snowflake\Snowflake;
use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Snowflake\Contracts\IdGeneratorInterface;
use Larmias\Snowflake\Sequence\RedisSequenceResolver;
use Godruoyi\Snowflake\SequenceResolver;
use Closure;

class IdGenerator implements IdGeneratorInterface
{
    /**
     * @var array
     */
    protected array $config = [
        'datacenter_id' => 1,
        'worker_id' => 1,
        'start_timestamp' => 1603506264000,
        'sequence' => RedisSequenceResolver::class,
    ];

    /**
     * @var int
     */
    protected int $workerId;

    /**
     * @var int
     */
    protected int $datacenterId;

    /**
     * @var int
     */
    protected int $startTimestamp;

    /**
     * @var Snowflake
     */
    protected Snowflake $snowflake;

    /**
     * @var SequenceResolver
     */
    protected SequenceResolver $sequenceResolver;

    /**
     * IdGenerator constructor.
     * @param ContainerInterface $container
     * @param ConfigInterface|null $config
     * @throws \Exception
     */
    public function __construct(protected ContainerInterface $container, ?ConfigInterface $config = null)
    {
        $this->config = \array_merge($this->config, $config ? $config->get('snowflake', []) : []);
        $this->datacenterId = (int)$this->getConfig('datacenter_id');
        $this->workerId = (int)$this->getConfig('worker_id');
        $this->startTimestamp = (int)$this->getConfig('start_timestamp');
        /** @var SequenceResolver $sequenceResolver */
        $sequenceResolver = $this->container->make($this->getConfig('sequence'), [], true);
        $this->sequenceResolver = $sequenceResolver;
        $this->newSnowflake();
    }

    /**
     * @return string
     */
    public function id(): string
    {
        return $this->snowflake->id();
    }

    /**
     * @param string $id
     * @param bool $transform
     * @return array
     */
    public function parseId(string $id, bool $transform = false): array
    {
        return $this->snowflake->parseId($id, $transform);
    }

    /**
     * @return SequenceResolver
     */
    public function getSequenceResolver(): SequenceResolver
    {
        return $this->sequenceResolver;
    }

    /**
     * @param SequenceResolver $sequenceResolver
     * @return self
     */
    public function setSequenceResolver(SequenceResolver $sequenceResolver): self
    {
        $this->sequenceResolver = $sequenceResolver;
        $this->snowflake->setSequenceResolver($sequenceResolver);
        return $this;
    }

    /**
     * @return int
     */
    public function getStartTimestamp(): int
    {
        return $this->startTimestamp;
    }

    /**
     * @param int $startTimestamp
     * @return self
     * @throws \Exception
     */
    public function setStartTimestamp(int $startTimestamp): self
    {
        $this->startTimestamp = $startTimestamp;
        $this->snowflake->setStartTimeStamp($startTimestamp);
        return $this;
    }

    /**
     * @return int
     */
    public function getWorkerId(): int
    {
        return $this->workerId;
    }

    /**
     * @param int $workerId
     * @return self
     * @throws \Exception
     */
    public function setWorkerId(int $workerId): self
    {
        $this->workerId = $workerId;
        $this->newSnowflake();
        return $this;
    }

    /**
     * @return int
     */
    public function getDatacenterId(): int
    {
        return $this->datacenterId;
    }

    /**
     * @param int $datacenterId
     * @return self
     * @throws \Exception
     */
    public function setDatacenterId(int $datacenterId): self
    {
        $this->datacenterId = $datacenterId;
        $this->newSnowflake();
        return $this;
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getConfig(string $key): mixed
    {
        if (!isset($this->config[$key])) {
            return null;
        }
        return $this->config[$key] instanceof Closure ? $this->container->invoke($this->config[$key]) : $this->config[$key];
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function newSnowflake(): void
    {
        $this->snowflake = new Snowflake($this->datacenterId, $this->workerId);
        $this->snowflake->setStartTimeStamp($this->startTimestamp);
        $this->snowflake->setSequenceResolver($this->sequenceResolver);
    }
}