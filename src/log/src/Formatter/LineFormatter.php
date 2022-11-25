<?php

declare(strict_types=1);

namespace Larmias\Log\Formatter;

use Larmias\Log\Contracts\FormatterInterface;
use Larmias\Utils\Str;

class LineFormatter implements FormatterInterface
{
    /**
     * LineFormatter constructor.
     *
     * @param string|null $format
     * @param string|null $dateFormat
     */
    public function __construct(protected ?string $format = null, protected ?string $dateFormat = null)
    {
        if (!$this->format) {
            $this->format = '[{datetime}][{channel}.{level}] {message} {context}' . PHP_EOL;
        }

        if (!$this->dateFormat) {
            $this->dateFormat = 'Y-m-d H:i:s';
        }
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
            ->setTimezone(new \DateTimeZone(date_default_timezone_get()))->format($this->dateFormat);
        return Str::template($this->format, [
            'datetime' => $datetime,
            'channel' => $channel,
            'level' => $level,
            'message' => $message,
            'context' => !empty($context) ? \var_export($context,true) : '',
        ]);
    }
}