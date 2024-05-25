<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan\EventDriver;

use Workerman\Events\Select as BaseSelect;
use Throwable;

class Select extends BaseSelect
{
    public function loop()
    {
        while (true) {
            if (empty($this->_allEvents) && empty($this->_signalEvents) && empty($this->_eventTimer)) {
                break;
            }

            if (\DIRECTORY_SEPARATOR === '/') {
                // Calls signal handlers for pending signals
                \pcntl_signal_dispatch();
            }

            $read = $this->_readFds;
            $write = $this->_writeFds;
            $except = $this->_exceptFds;
            $ret = false;

            if ($read || $write || $except) {
                // Waiting read/write/signal/timeout events.
                try {
                    $ret = @stream_select($read, $write, $except, 0, $this->_selectTimeout);
                } catch (Throwable) {
                }

            } else {
                $this->_selectTimeout >= 1 && usleep($this->_selectTimeout);
            }

            if (!$this->_scheduler->isEmpty()) {
                $this->tick();
            }

            if (!$ret) {
                continue;
            }

            if ($read) {
                foreach ($read as $fd) {
                    $fd_key = (int)$fd;
                    if (isset($this->_allEvents[$fd_key][self::EV_READ])) {
                        \call_user_func_array($this->_allEvents[$fd_key][self::EV_READ][0],
                            array($this->_allEvents[$fd_key][self::EV_READ][1]));
                    }
                }
            }

            if ($write) {
                foreach ($write as $fd) {
                    $fd_key = (int)$fd;
                    if (isset($this->_allEvents[$fd_key][self::EV_WRITE])) {
                        \call_user_func_array($this->_allEvents[$fd_key][self::EV_WRITE][0],
                            array($this->_allEvents[$fd_key][self::EV_WRITE][1]));
                    }
                }
            }

            if ($except) {
                foreach ($except as $fd) {
                    $fd_key = (int)$fd;
                    if (isset($this->_allEvents[$fd_key][self::EV_EXCEPT])) {
                        \call_user_func_array($this->_allEvents[$fd_key][self::EV_EXCEPT][0],
                            array($this->_allEvents[$fd_key][self::EV_EXCEPT][1]));
                    }
                }
            }
        }
    }
}