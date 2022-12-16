<?php

declare(strict_types=1);

namespace Larmias\Engine\Contracts;

interface WorkerInterface
{
	/**
	 * @param  int    $workerId 
	 * @return void
	 */
    public function onWorkerStart(int $workerId): void;
    
    /**
     * @param string $event
     * @param array $args
     */
    public function trigger(string $event, array $args = []): void;

    /**
     * @return int
     */
    public function getWorkerId(): int;
}