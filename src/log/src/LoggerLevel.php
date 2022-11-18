<?php

declare(strict_types=1);

namespace Larmias\Log;

use Psr\Log\LogLevel;

class LoggerLevel extends LogLevel
{
    public const SQL = 'sql';
}