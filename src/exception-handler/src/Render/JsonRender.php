<?php

declare(strict_types=1);

namespace Larmias\ExceptionHandler\Render;

use Larmias\Support\Codec\Json;
use Throwable;
use function Larmias\Support\format_exception;
use function Larmias\Support\println;

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
            return Json::encode($data);
        } catch (Throwable) {
            println(format_exception($e));
            return '';
        }
    }
}