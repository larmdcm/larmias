<?php

declare(strict_types=1);

namespace Larmias\Log\Formatter;

use Larmias\Log\Contracts\FormatterInterface;
use Larmias\Stringable\Str;
use function array_merge;
use function var_export;

class LineFormatter implements FormatterInterface
{
    /**
     * @var array|string[]
     */
    protected array $config = [
        'format' => '[${datetime}][${channel}.${level}] ${message} ${context}' . PHP_EOL,
        'date_format' => 'Y-m-d H:i:s',
    ];

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
    }


    /**
     * @param string|\Stringable $message
     * @param string $channel
     * @param string $level
     * @param array $context
     * @return string
     */
    public function format(string|\Stringable $message, string $channel, string $level, array $context = []): string
    {
        $message = (string)$message;
        if (!empty($context)) {
            $message = Str::template($message, $context);
        }
        $datetime = \DateTime::createFromFormat('0.u00 U', microtime())
            ->setTimezone(new \DateTimeZone(date_default_timezone_get()))->format($this->config['date_format']);
        return Str::template($this->config['format'], [
            'datetime' => $datetime,
            'channel' => $channel,
            'level' => $level,
            'message' => $message,
            'context' => !empty($context) ? var_export($context, true) : '',
        ]);
    }
}