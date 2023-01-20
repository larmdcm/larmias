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
        $data['tables'] = $this->getDataTable();
        return $data;
    }

    /**
     * 获取出错文件内容
     *
     * @param Throwable $exception
     * @param int $showLine
     * @return array
     */
    protected function getSourceCode(Throwable $exception, int $showLine = 15): array
    {
        $data = [
            'line' => $exception->getLine(),
            'source' => [],
            'first' => 0,
            'highlight' => 0,
        ];
        try {
            $source = [];
            $contents = \file($exception->getFile()) ?: [];
            for ($i = $showLine; $i > 0; $i--) {
                $line = $data['line'] - $i;
                if (!isset($contents[$line])) {
                    break;
                }
                if ($i == $showLine) {
                    $data['first'] = $line - 1;
                }
                $source[] = $contents[$line];
            }
            $source[] = $contents[$data['line']];
            $data['highlight'] = \count($source);
            for ($i = 1; $i <= $showLine; $i++) {
                $line = $data['line'] + $i;
                if (!isset($contents[$line])) {
                    break;
                }
                $source[] = $contents[$line];
            }
            $data['source'] = $source;
        } catch (Throwable $e) {
        }

        return $data;
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