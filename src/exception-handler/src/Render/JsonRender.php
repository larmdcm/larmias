<?php

declare(strict_types=1);

namespace Larmias\ExceptionHandler\Render;

use Throwable;

class JsonRender extends Render
{
    /**
     * @param Throwable $e
     * @return string
     */
    public function render(Throwable $e): string
    {
        try {
            $data = $this->getData($e);
            return \json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return ' ';
        }
    }
}