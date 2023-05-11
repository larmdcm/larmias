<?php

declare(strict_types=1);

namespace Larmias\Framework\Logger;

use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\StdoutLoggerInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LogLevel;
use function in_array;
use function array_keys;
use function str_replace;
use function array_map;
use function sprintf;

class StdoutLogger implements StdoutLoggerInterface
{
    /**
     * @var string[]
     */
    protected array $tags = [
        'component',
    ];

    /**
     * @param ConfigInterface|null $config
     * @param OutputInterface|null $output
     */
    public function __construct(protected ?ConfigInterface $config = null, protected ?OutputInterface $output = null)
    {
        if (!$this->output) {
            $this->output = new ConsoleOutput();
        }
    }

    /**
     * @param \Stringable|string $message
     * @param array $context
     * @return void
     */
    public function emergency(\Stringable|string $message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * @param \Stringable|string $message
     * @param array $context
     * @return void
     */
    public function alert(\Stringable|string $message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * @param \Stringable|string $message
     * @param array $context
     * @return void
     */
    public function critical(\Stringable|string $message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * @param \Stringable|string $message
     * @param array $context
     * @return void
     */
    public function error(\Stringable|string $message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * @param \Stringable|string $message
     * @param array $context
     * @return void
     */
    public function warning(\Stringable|string $message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * @param \Stringable|string $message
     * @param array $context
     * @return void
     */
    public function notice(\Stringable|string $message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * @param \Stringable|string $message
     * @param array $context
     * @return void
     */
    public function info(\Stringable|string $message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * @param \Stringable|string $message
     * @param array $context
     * @return void
     */
    public function debug(\Stringable|string $message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * @param \Stringable|string $message
     * @param array $context
     * @return void
     */
    public function sql(\Stringable|string $message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * @param $level
     * @param \Stringable|string $message
     * @param array $context
     * @return void
     */
    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $config = $this->config?->get('logger', ['level' => []]);
        if ($config && !empty($config['level']) && !in_array($level, $config['level'], true)) {
            return;
        }
        $keys = array_keys($context);
        $tags = [];
        foreach ($keys as $k => $key) {
            if (in_array($key, $this->tags, true)) {
                $tags[$key] = $context[$key];
                unset($keys[$k]);
            }
        }
        $search = array_map(fn($key) => sprintf('{%s}', $key), $keys);
        $message = str_replace($search, $context, $this->getMessage((string)$message, $level, $tags));
        $this->writeln($message);
    }

    /**
     * @param string $message
     * @return void
     */
    public function write(string $message): void
    {
        $this->output->write($message);
    }

    /**
     * @param string $message
     * @return void
     */
    public function writeln(string $message): void
    {
        $this->output->writeln($message);
    }

    /**
     * @param string $message
     * @param string $level
     * @param array $tags
     * @return string
     */
    protected function getMessage(string $message, string $level = LogLevel::INFO, array $tags = []): string
    {
        $tag = match ($level) {
            LogLevel::EMERGENCY, LogLevel::ALERT, LogLevel::CRITICAL => 'error',
            LogLevel::ERROR => 'fg=red',
            LogLevel::WARNING, LogLevel::NOTICE => 'comment',
            default => 'info',
        };

        $template = sprintf('<%s>[%s]</>', $tag, strtoupper($level));
        $implodedTags = '';
        foreach ($tags as $value) {
            $implodedTags .= (' [' . $value . ']');
        }

        return sprintf($template . $implodedTags . ' %s', $message);
    }
}
