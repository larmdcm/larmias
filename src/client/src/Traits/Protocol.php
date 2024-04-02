<?php

declare(strict_types=1);

namespace Larmias\Client\Traits;

use Larmias\Contracts\ProtocolInterface;
use Larmias\Support\ProtocolHandler;

trait Protocol
{
    /**
     * @var ProtocolInterface|null
     */
    protected ?ProtocolInterface $protocol = null;

    /**
     * @var ProtocolHandler
     */
    protected ProtocolHandler $protocolHandler;

    /**
     * @param array $options
     * @return void
     */
    public function initProtocolHandler(array $options = []): void
    {
        if (!isset($this->protocolHandler)) {
            $this->protocolHandler = new ProtocolHandler();
        }

        if (array_key_exists('protocol', $options)) {
            $this->protocol = $options['protocol'] ? new $options['protocol'] : null;
            $this->protocolHandler->setProtocol($this->protocol);
        }

        if (array_key_exists('max_package_size', $options)) {
            $this->protocolHandler->setMaxPackageSize((int)$options['max_package_size']);
        }
    }
}