<?php

declare(strict_types=1);

namespace Larmias\ExceptionHandler;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ExceptionHandlerInterface;
use Larmias\ExceptionHandler\Contracts\ExceptionHandlerDispatcherInterface;
use Throwable;
use function is_object;

class ExceptionHandlerDispatcher implements ExceptionHandlerDispatcherInterface
{
    /**
     * @param ContainerInterface $container
     */
    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * 异常调度.
     * @param Throwable $e
     * @param array $handlers
     * @param mixed|null $args
     * @return mixed
     * @throws Throwable
     */
    public function dispatch(Throwable $e, array $handlers, mixed $args = null): mixed
    {
        $result = null;
        foreach ($handlers as $handler) {
            /** @var ExceptionHandlerInterface $instance */
            $instance = is_object($handler) ? $handler : $this->container->get($handler);
            if (!($instance instanceof ExceptionHandlerInterface) || !$instance->isValid($e)) {
                continue;
            }
            $result = $instance->handle($e, $result, $args);
            if ($instance->isPropagationStopped()) {
                break;
            }
        }

        return $result;
    }
}