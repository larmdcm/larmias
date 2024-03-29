<?php

declare(strict_types=1);

namespace Larmias\Encryption\Driver;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\EncoderInterface;
use Larmias\Contracts\Encryption\EncryptorInterface;
use Larmias\Contracts\PackerInterface;
use function array_merge;

abstract class Driver implements EncryptorInterface, EncoderInterface
{
    /**
     * @var array|string[]
     */
    protected array $config = [
        'key' => null,
        'packer' => null,
    ];

    /**
     * @var PackerInterface|null
     */
    protected ?PackerInterface $packer = null;

    /**
     * @param ContainerInterface $container
     * @param array $config
     */
    public function __construct(protected ContainerInterface $container, array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        if ($this->config['packer']) {
            /** @var PackerInterface $packer */
            $packer = $this->container->make($this->config['packer']);
            $this->packer = $packer;
        }
    }

    /**
     * @param mixed $data
     * @return string
     */
    public function encode(mixed $data): string
    {
        return $this->packer ? $this->packer->pack($data) : $data;
    }

    /**
     * @param string $data
     * @return string
     */
    public function decode(string $data): string
    {
        return $this->packer ? $this->packer->unpack($data) : $data;
    }
}