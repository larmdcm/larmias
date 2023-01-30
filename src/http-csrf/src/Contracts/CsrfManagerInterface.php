<?php

declare(strict_types=1);

namespace Larmias\Http\CSRF\Contracts;

interface CsrfManagerInterface
{
    /**
     * @return void
     */
    public function init(): void;

    /**
     * 重新生成token
     *
     * @return void
     */
    public function regenerateToken(): void;

    /**
     * @return string|null
     */
    public function getToken(): ?string;

    /**
     * @param string $tokenName
     * @return void
     */
    public function setTokenName(string $tokenName): void;

    /**
     * @return string
     */
    public function getTokenName(): string;
}