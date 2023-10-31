<?php

declare(strict_types=1);

namespace Larmias\ExceptionHandler\Render;

use Throwable;
use function realpath;
use function Larmias\Support\println;
use function Larmias\Support\format_exception;

class HtmlRender extends Render
{
    /**
     * @param Throwable $e
     * @return string
     */
    public function render(Throwable $e): string
    {
        try {
            $data = $this->getData($e);
            $data['resource_path'] = realpath(__DIR__ . '/../../resources');
            $data['source_code'] = $this->getSourceCode($e);
            ob_start();
            include $data['resource_path'] . '/views/html_render.php';
            $content = ob_get_clean();
            return $content === false ? ' ' : $content;
        } catch (Throwable $e) {
            println(format_exception($e));
            return '';
        }
    }
}