<?php

declare(strict_types=1);

namespace Larmias\Captcha;

use Stringable;

class Result implements Stringable
{
    /**
     * @var string
     */
    protected string $codeHash;

    /**
     * Result constructor.
     * @param string $content
     * @param string $code
     * @param string $mimeType
     */
    public function __construct(protected string $content, protected string $code, protected $mimeType = 'image/png')
    {
        $this->codeHash = \substr(\md5($this->code . \time() . \mt_rand(100000, 999999)), 8, 16);
    }

    /**
     * @return string
     */
    public function toImageBase64(): string
    {
        return 'data:' . $this->mimeType . ';base64,' . $this->toBase64();
    }

    /**
     * @return string
     */
    public function toBase64(): string
    {
        return \base64_encode($this->getContent());
    }

    /**
     * @return string
     */
    public function getCodeHash(): string
    {
        return $this->codeHash;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toBase64();
    }
}