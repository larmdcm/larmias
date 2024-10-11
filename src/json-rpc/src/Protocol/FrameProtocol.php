<?php

declare(strict_types=1);

namespace Larmias\JsonRpc\Protocol;

use Larmias\Codec\Protocol\FrameProtocol as BaseFrameProtocol;
use Larmias\JsonRpc\Contracts\ProtocolInterface;

class FrameProtocol extends BaseFrameProtocol implements ProtocolInterface
{
}