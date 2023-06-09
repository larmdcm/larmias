<?php

declare(strict_types=1);

namespace Larmias\ExceptionHandler\Render;

use Larmias\Contracts\ConfigInterface;
use Larmias\ExceptionHandler\Contracts\RenderInterface;
use Larmias\Utils\Str;
use Throwable;
use function array_merge;
use function file;
use function get_class;
use function is_array;
use function count;
use function Larmias\Utils\println;
use function Larmias\Utils\format_exception;

abstract class Render implements RenderInterface
{
    /**
     * @var array
     */
    protected array $tables = [];

    /**
     * @var array
     */
    protected array $ignoreVars = [];

    /**
     * @param ConfigInterface|array|null $config
     */
    public function __construct(ConfigInterface|array|null $config = null)
    {
        if ($config) {
            $config = is_array($config) ? $config : $config->get('exceptions', []);
            $this->ignoreVars = array_merge($this->ignoreVars, $config['ignore_vars'] ?? []);
        }
    }

    /**
     * @param string $name
     * @param callable $callback
     * @return $this
     */
    public function addDataTableCallback(string $name, callable $callback): self
    {
        $this->tables[$name][] = $callback;
        return $this;
    }

    /**
     * @return array
     */
    public function getDataOfTable(): array
    {
        $data = [];
        foreach ($this->tables as $name => $tables) {
            foreach ($tables as $callback) {
                $data[$name] = array_merge($data[$name] ?? [], $this->filterData((array)$callback()));
            }
        }

        return $data;
    }

    /**
     * @param Throwable $e
     * @return array
     */
    public function getData(Throwable $e): array
    {
        $data = $this->collectExceptionToArray($e);
        $data['tables'] = $this->getDataOfTable();
        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function filterData(array $data): array
    {
        foreach ($data as $key => $item) {
            if (Str::is($this->ignoreVars, $key)) {
                unset($data[$key]);
            }
        }

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
            $contents = file($exception->getFile()) ?: [];
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
            $data['highlight'] = count($source);
            for ($i = 1; $i <= $showLine; $i++) {
                $line = $data['line'] + $i;
                if (!isset($contents[$line])) {
                    break;
                }
                $source[] = $contents[$line];
            }
            $data['source'] = $source;
        } catch (Throwable $e) {
            println(format_exception($e));
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
                'name' => get_class($nextException),
                'file' => $nextException->getFile(),
                'line' => $nextException->getLine(),
                'code' => $nextException->getCode(),
                'message' => $nextException->getMessage(),
                'trace' => $nextException->getTrace(),
            ];
        } while ($nextException = $nextException->getPrevious());

        return [
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'traces' => $traces,
        ];
    }
}