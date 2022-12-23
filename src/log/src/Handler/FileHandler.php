<?php

declare(strict_types=1);

namespace Larmias\Log\Handler;

use Larmias\Log\Contracts\LoggerHandlerInterface;

class FileHandler implements LoggerHandlerInterface
{
    protected array $config = [
        'path' => null,
        'file_size' => 0,
        'single' => false,
        'max_files' => 0,
        'apart_level' => [],
    ];

    public function __construct(array $config = [])
    {
        $this->config = \array_merge($this->config, $config);
        if (\is_null($this->config['path'])) {
            $this->config['path'] = \sys_get_temp_dir();
        }

        if (\substr($this->config['path'], -1) != DIRECTORY_SEPARATOR) {
            $this->config['path'] .= DIRECTORY_SEPARATOR;
        }
    }

    public function save(array $logs): bool
    {
        $destination = $this->getMasterLogFile();

        $path = \dirname($destination);
        !\is_dir($path) && \mkdir($path, 0755, true);

        $info = [];
        foreach ($logs as $type => $val) {
            if (true === $this->config['apart_level'] || in_array($type, $this->config['apart_level'])) {
                // 独立记录的日志级别
                $filename = $this->getApartLevelFile($path, $type);
                $this->write([$type => $val], $filename);
                continue;
            }
            $info[$type] = $val;
        }
        if ($info) {
            $this->write($info, $destination);
        }
        return true;
    }

    /**
     * 日志写入
     *
     * @param array $logs 日志信息
     * @param string $destination 日志文件
     * @return bool
     */
    protected function write(array $logs, string $destination): bool
    {
        // 检测日志文件大小，超过配置大小则备份日志文件重新生成
        $this->checkLogSize($destination);

        $info = [];

        foreach ($logs as $val) {
            $info[] = implode(PHP_EOL,array_column($val,'content'));
        }

        $message = \implode(PHP_EOL, $info);

        return \error_log($message, 3, $destination);
    }

    /**
     * 获取主日志文件名
     *
     * @return string
     */
    protected function getMasterLogFile(): string
    {

        if ($this->config['max_files']) {
            $files = \glob($this->config['path'] . '*.log');

            try {
                if (\count($files) > $this->config['max_files']) {
                    \set_error_handler(function ($errno, $errstr, $errfile, $errline) {
                    });
                    \unlink($files[0]);
                    \restore_error_handler();
                }
            } catch (\Exception $e) {
                //
            }
        }

        if ($this->config['single']) {
            $name = \is_string($this->config['single']) ? $this->config['single'] : 'single';
            $destination = $this->config['path'] . $name . '.log';
        } else {

            if ($this->config['max_files']) {
                $filename = \date('Ymd') . '.log';
            } else {
                $filename = \date('Ym') . DIRECTORY_SEPARATOR . \date('d') . '.log';
            }

            $destination = $this->config['path'] . $filename;
        }

        return $destination;
    }

    /**
     * 获取独立日志文件名
     *
     * @param string $path 日志目录
     * @param string $type 日志类型
     * @return string
     */
    protected function getApartLevelFile(string $path, string $type): string
    {

        if ($this->config['single']) {
            $name = \is_string($this->config['single']) ? $this->config['single'] : 'single';

            $name .= '_' . $type;
        } elseif ($this->config['max_files']) {
            $name = \date('Ymd') . '_' . $type;
        } else {
            $name = \date('d') . '_' . $type;
        }

        return $path . DIRECTORY_SEPARATOR . $name . '.log';
    }

    /**
     * 检查日志文件大小并自动生成备份文件
     *
     * @param string $destination 日志文件
     * @return void
     */
    protected function checkLogSize(string $destination): void
    {
        if ($this->config['file_size'] > 0 && \is_file($destination) && \floor($this->config['file_size']) <= \filesize($destination)) {
            try {
                \rename($destination, dirname($destination) . DIRECTORY_SEPARATOR . time() . '-' . basename($destination));
                \clearstatcache();
            } catch (\Exception $e) {
                //
            }
        }
    }
}