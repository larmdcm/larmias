<?php

declare(strict_types=1);

namespace Larmias\Encryption\Drivers;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\DataCodingInterface;
use Larmias\Contracts\Encryption\EncryptorInterface;

abstract class Driver implements EncryptorInterface, DataCodingInterface
{
    /**
     * @var array|string[]
     */
    protected array $config = [
        'key' => null,
        'data_coding' => null,
    ];

    /**
     * @var DataCodingInterface|null
     */
    protected ?DataCodingInterface $dataCoding = null;

    /**
     * @param ContainerInterface $container
     * @param array $config
     */
    public function __construct(protected ContainerInterface $container, array $config = [])
    {
        $this->config = \array_merge($this->config, $config);
        if ($this->config['data_coding']) {
            /** @var DataCodingInterface $dataCoding */
            $dataCoding = $this->container->make($this->config['data_coding']);
            $this->dataCoding = $dataCoding;
        }
    }

    /**
     * @param string $data
     * @return string
     */
    public function encode(string $data): string
    {
        return $this->dataCoding ? $this->dataCoding->encode($data) : $data;
    }

    /**
     * @param string $data
     * @return string
     */
    public function decode(string $data): string
    {
        return $this->dataCoding ? $this->dataCoding->decode($data) : $data;
    }
}