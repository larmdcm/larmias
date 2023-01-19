<?php

declare(strict_types=1);

namespace Larmias\ExceptionHandler\Render;

use Larmias\ExceptionHandler\Contracts\RenderInterface;
use Throwable;

abstract class Render implements RenderInterface
{
    protected array $tables = [];

    public function addDataTableCallback(string $name, callable $callback): self
    {
        $this->tables[$name][] = $callback;
        return $this;
    }

    public function getDataTable(): array
    {
        $data = [];
        foreach ($this->tables as $name => $tables) {
            foreach ($tables as $callback) {
                $data[$name] = \array_merge($data[$name] ?? [], (array)$callback());
            }
        }

        return $data;
    }

    public function getData(Throwable $e): array
    {
        $data = $this->collectExceptionToArray($e);
        $data['source_code'] = $this->getSourceCode($e);
        $data['tables'] = $this->getDataTable();
        return $data;
    }

    /**
     * 获取出错文件内容
     *
     * @param Throwable $exception
     * @return array
     */
    protected function getSourceCode(Throwable $exception): array
    {
        $line = $exception->getLine();
        $first = ($line - 9 > 0) ? $line - 9 : 1;

        try {
            $contents = \file($exception->getFile()) ?: [];
            $source = [
                'line' => $line,
                'source' => \array_slice($contents, $first - 1, 19),
            ];
        } catch (Throwable $e) {
            $source = [];
        }

        return $source;
    }

    /**
     * 收集异常信息
     *
     * @param Throwable $exception
     * @return array
     */
    protected function collectExceptionToArray(Throwable $exception): array
    {
        $traces = [];
        $nextException = $exception;
        do {
            $traces[] = [
                'name' => \get_class($nextException),
                'file' => $nextException->getFile(),
                'line' => $nextException->getLine(),
                'code' => $nextException->getCode(),
                'message' => $nextException->getMessage(),
                'trace' => $nextException->getTrace(),
            ];
        } while ($nextException = $nextException->getPrevious());
        $data = [
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'exception' => \get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'traces' => $traces,
        ];
        return $data;
    }
}