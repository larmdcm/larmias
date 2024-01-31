<?php

declare(strict_types=1);

namespace Larmias\Encryption\Driver;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\EncoderInterface;
use Larmias\Contracts\Encryption\EncryptorInterface;
use function array_merge;

abstract class Driver implements EncryptorInterface, EncoderInterface
{
    /**
     * @var array|string[]
     */
    protected array $config = [
        'key' => null,
        'encoder' => null,
    ];

    /**
     * @var EncoderInterface|null
     */
    protected ?EncoderInterface $dataCoding = null;

    /**
     * @param ContainerInterface $container
     * @param array $config
     */
    public function __construct(protected ContainerInterface $container, array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        if ($this->config['encoder']) {
            /** @var EncoderInterface $dataCoding */
            $dataCoding = $this->container->make($this->config['encoder']);
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