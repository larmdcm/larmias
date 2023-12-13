<?php

declare(strict_types=1);

namespace Larmias\Http\CSRF;

use Larmias\Contracts\SessionInterface;
use Larmias\Http\CSRF\Contracts\CsrfManagerInterface;

class CsrfManager implements CsrfManagerInterface
{
    /**
     * @var string
     */
    protected string $tokenName = '__token';

    /**
     * CsrfManager constructor.
     *
     * @param SessionInterface $session
     */
    public function __construct(protected SessionInterface $session)
    {
    }

    /**
     * @return void
     */
    public function init(): void
    {
        if (!$this->session->has($this->tokenName)) {
            $this->regenerateToken();
        }
    }

    /**
     * 重新生成token
     * @return void
     */
    public function regenerateToken(): void
    {
        $this->session->set($this->tokenName, $this->session->generateSessionId());
    }

    /**
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->session->get($this->tokenName);
    }

    /**
     * @param string $tokenName
     * @return void
     */
    public function setTokenName(string $tokenName): void
    {
        $this->tokenName = $tokenName;
    }

    /**
     * @return string
     */
    public function getTokenName(): string
    {
        return $this->tokenName;
    }
}