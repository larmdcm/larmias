<?php

declare(strict_types=1);

namespace Larmias\Support;

use Larmias\Contracts\Protocol\ProtocolException;
use Larmias\Contracts\ProtocolInterface;
use Larmias\Stringable\StringBuffer;
use Closure;
use Throwable;

class ProtocolHandler
{
    /**
     * @var int
     */
    protected int $currPackageLen = 0;

    /**
     * @var StringBuffer
     */
    protected StringBuffer $buffer;

    /**
     * @var mixed
     */
    protected mixed $exceptionHandler = null;

    /**
     * @param ProtocolInterface|null $protocol
     * @param int $maxPackageSize
     */
    public function __construct(protected ?ProtocolInterface $protocol = null, protected int $maxPackageSize = 0)
    {
        $this->buffer = new StringBuffer();
    }

    /**
     * @param mixed $data
     * @param Closure $callback
     * @return void
     * @throws Throwable
     */
    public function handle(mixed $data, Closure $callback): void
    {
        try {
            if (!$this->protocol) {
                $callback($data);
                return;
            }

            $this->buffer->append($data);

            while (!$this->buffer->isEmpty()) {
                if ($this->currPackageLen <= 0) {
                    $this->currPackageLen = $this->protocol->input($this->buffer->toString());
                }

                if ($this->currPackageLen <= 0 || $this->currPackageLen > $this->buffer->size()) {
                    break;
                }

                if ($this->maxPackageSize > 0 && $this->currPackageLen > $this->maxPackageSize) {
                    throw new ProtocolException('Error package length = ' . $this->currPackageLen);
                }

                $packageData = $this->buffer->peek(0, $this->currPackageLen);
                $this->buffer->take($this->currPackageLen);
                $this->currPackageLen = 0;
                $callback($this->protocol->unpack($packageData));
            }
        } catch (Throwable $e) {
            if ($this->exceptionHandler) {
                call_user_func($this->exceptionHandler, $e, $data);
                return;
            }
            throw $e;
        }
    }

    /**
     * @param callable $exceptionHandler
     * @return void
     */
    public function whenException(callable $exceptionHandler): void
    {
        $this->exceptionHandler = $exceptionHandler;
    }

    /**
     * @return ProtocolInterface|null
     */
    public function getProtocol(): ?ProtocolInterface
    {
        return $this->protocol;
    }

    /**
     * @param ProtocolInterface|null $protocol
     */
    public function setProtocol(?ProtocolInterface $protocol): void
    {
        $this->protocol = $protocol;
    }

    /**
     * @return int
     */
    public function getMaxPackageSize(): int
    {
        return $this->maxPackageSize;
    }

    /**
     * @param int $maxPackageSize
     */
    public function setMaxPackageSize(int $maxPackageSize): void
    {
        $this->maxPackageSize = $maxPackageSize;
    }
}