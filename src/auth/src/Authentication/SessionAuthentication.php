<?php

declare(strict_types=1);

namespace Larmias\Auth\Authentication;

use Larmias\Contracts\SessionInterface;

class SessionAuthentication extends Authentication
{
    /**
     * @var SessionInterface
     */
    protected SessionInterface $session;

    /**
     * @param SessionInterface $session
     */
    public function initialize(SessionInterface $session): void
    {
        $this->session = $session;
    }

    /**
     * @param mixed $parameter
     * @return mixed
     */
    public function getCredentials(mixed $parameter): mixed
    {
        return $this->session->get($this->config['auth_name']);
    }
}