<?php

declare(strict_types=1);

namespace Larmias\ExceptionHandler\Annotation\Handler;

use Larmias\Contracts\Annotation\AnnotationHandlerInterface;
use Larmias\Contracts\ConfigInterface;
use Larmias\ExceptionHandler\Annotation\ExceptionHandler;

class ExceptionHandlerAnnotationHandler implements AnnotationHandlerInterface
{
    public function __construct(protected ?ConfigInterface $config = null)
    {
    }

    public function collect(array $param): void
    {
        if (!$this->config) {
            return;
        }

        /** @var ExceptionHandler $annotation */
        $annotation = $param['value'][0];
        $name = 'exceptions.handler';
        if ($annotation->name) {
            $name .= '.' . $annotation->name;
        }
        $this->config->push($name, $param['class']);
    }

    public function handle(): void
    {
        // TODO: Implement handle() method.
    }
}