<?php

declare(strict_types=1);

namespace LarmiasTest\Log;

use Larmias\Log\Logger;
use PHPUnit\Framework\TestCase;
use function Larmias\Support\make;

class LoggerTest extends TestCase
{
    /**
     * @return void
     */
    public function testRecord(): void
    {
        /** @var Logger $logger */
        $logger = make(Logger::class);
        $logger->info('hello');
        $logger->notice('world');
        $logger->debug('debug info');
        $this->assertTrue(true);
    }

    /**
     * @return void
     */
    public function testChannelRecord(): void
    {
        /** @var Logger $logger */
        $logger = make(Logger::class);
        $channel = $logger->channel('stdout');
        $channel->setRealtimeWrite(false);
        $channel->info('hello');
        $channel->notice('world');
        $channel->debug('debug info');
        if (!$channel->isRealtimeWrite()) {
            $channel->save();
        }
        $this->assertTrue(true);
    }
}