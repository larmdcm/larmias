<?php

declare(strict_types=1);

namespace Larmias\View\Driver\Blade\Engines;

interface EngineInterface
{
    /**
     * Get the evaluated contents of the view.
     *
     * @param string $path
     * @param array $data
     * @return string
     */
    public function get(string $path, array $data = []): string;
}
