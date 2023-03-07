<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

use Larmias\Engine\Swoole\Contracts\ServerInterface;

abstract class Server extends Worker implements ServerInterface
{
}