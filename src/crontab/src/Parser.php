<?php

declare(strict_types=1);

namespace Larmias\Crontab;

use Carbon\Carbon;
use InvalidArgumentException;
use Larmias\Crontab\Contracts\ParserInterface;

use function preg_match;
use function trim;
use function str_contains;
use function explode;
use function max;
use function array_merge;
use function time;
use function preg_split;
use function count;
use function is_numeric;
use function in_array;

class Parser implements ParserInterface
{
    /**
     *
     * @param string $rule
     *                              0    1    2    3    4    5
     *                              *    *    *    *    *    *
     *                              -    -    -    -    -    -
     *                              |    |    |    |    |    |
     *                              |    |    |    |    |    +----- day of week (0 - 6) (Sunday=0)
     *                              |    |    |    |    +----- month (1 - 12)
     *                              |    |    |    +------- day of month (1 - 31)
     *                              |    |    +--------- hour (0 - 23)
     *                              |    +----------- min (0 - 59)
     *                              +------------- sec (0-59)
     * @param mixed $startTime
     * @return Carbon[]
     */
    public function parse(string $rule, mixed $startTime = null): array
    {
        if (!$this->isValid($rule)) {
            throw new InvalidArgumentException('Invalid cron rule: ' . $rule);
        }
        $startTime = $this->parseStartTime($startTime);
        $date = $this->parseDate($rule);
        if (in_array((int)date('i', $startTime), $date['minutes'])
            && in_array((int)date('G', $startTime), $date['hours'])
            && in_array((int)date('j', $startTime), $date['day'])
            && in_array((int)date('w', $startTime), $date['week'])
            && in_array((int)date('n', $startTime), $date['month'])
        ) {
            $result = [];
            foreach ($date['second'] as $second) {
                $result[] = Carbon::createFromTimestamp($startTime + $second);
            }
            return $result;
        }
        return [];
    }

    /**
     * @param string $rule
     * @return bool
     */
    public function isValid(string $rule): bool
    {
        if (!preg_match('/^((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)$/i', trim($rule))) {
            if (!preg_match('/^((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)$/i', trim($rule))) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param mixed $startTime
     * @return int
     */
    protected function parseStartTime(mixed $startTime): int
    {
        if ($startTime === null) {
            $startTime = time();
        } else if ($startTime instanceof Carbon) {
            return $startTime->getTimestamp();
        }
        if (!is_numeric($startTime)) {
            throw new InvalidArgumentException("\$startTime have to be a valid unix timestamp ({$startTime} given)");
        }
        return (int)$startTime;
    }

    /**
     * Parse each segment of crontab string.
     * @return int[]
     */
    protected function parseSegment(string $string, int $min, int $max, int $start = null): array
    {
        if ($start === null || $start < $min) {
            $start = $min;
        }
        $result = [];
        if ($string === '*') {
            for ($i = $start; $i <= $max; ++$i) {
                $result[] = $i;
            }
        } elseif (str_contains($string, ',')) {
            $exploded = explode(',', $string);
            foreach ($exploded as $value) {
                if (str_contains($value, '/') || str_contains($string, '-')) {
                    $result = array_merge($result, $this->parseSegment($value, $min, $max, $start));
                    continue;
                }

                if (trim($value) === '' || !$this->between((int)$value, max($min, $start), $max)) {
                    continue;
                }
                $result[] = (int)$value;
            }
        } elseif (str_contains($string, '/')) {
            $exploded = explode('/', $string);
            if (str_contains($exploded[0], '-')) {
                [$nMin, $nMax] = explode('-', $exploded[0]);
                $nMin > $min && $min = (int)$nMin;
                $nMax < $max && $max = (int)$nMax;
            }
            // If the value of start is larger than the value of min, the value of start should equal with the value of min.
            $start < $min && $start = $min;
            for ($i = $start; $i <= $max;) {
                $result[] = $i;
                $i += (int)$exploded[1];
            }
        } elseif (str_contains($string, '-')) {
            $result = array_merge($result, $this->parseSegment($string . '/1', $min, $max, $start));
        } elseif ($this->between((int)$string, max($min, $start), $max)) {
            $result[] = (int)$string;
        }
        return $result;
    }

    /**
     * Determine if the $value is between in $min and $max
     * @return bool
     */
    protected function between(int $value, int $min, int $max): bool
    {
        return $value >= $min && $value <= $max;
    }

    /**
     * @param string $rule
     * @return array|\int[][]
     */
    protected function parseDate(string $rule): array
    {
        $cron = preg_split('/[\\s]+/i', trim($rule));
        if (count($cron) == 6) {
            $date = [
                'second' => $this->parseSegment($cron[0], 0, 59),
                'minutes' => $this->parseSegment($cron[1], 0, 59),
                'hours' => $this->parseSegment($cron[2], 0, 23),
                'day' => $this->parseSegment($cron[3], 1, 31),
                'month' => $this->parseSegment($cron[4], 1, 12),
                'week' => $this->parseSegment($cron[5], 0, 6),
            ];
        } else {
            $date = [
                'second' => [1 => 0],
                'minutes' => $this->parseSegment($cron[0], 0, 59),
                'hours' => $this->parseSegment($cron[1], 0, 23),
                'day' => $this->parseSegment($cron[2], 1, 31),
                'month' => $this->parseSegment($cron[3], 1, 12),
                'week' => $this->parseSegment($cron[4], 0, 6),
            ];
        }
        return $date;
    }
}