<?php

declare(strict_types=1);

namespace Larmias\ExceptionHandler\Render;

use Larmias\Utils\Codec\Json;
use Throwable;
use function Larmias\Utils\println;
use function Larmias\Utils\format_exception;

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