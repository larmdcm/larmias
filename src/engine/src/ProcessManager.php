<?php

declare(strict_types=1);

namespace Larmias\Engine;

class ProcessManager
{
    /**
     * @var bool
     */
    protected static bool $running = true;

    /**
     * @param bool $running
     * @return void
     */
    public static function setRunning(bool $running): void
    {
        static::$running = $running;
    }

    /**
     * @return bool
     */
    public static function isRunning(): bool
    {
        return static::$running;
    }
}