<?php

declare(strict_types=1);

namespace Larmias\Auth\Authentication;

use Larmias\Contracts\Auth\IdentityRepositoryInterface;
use Larmias\Contracts\SessionInterface;

class SessionAuthentication extends Authentication
{
    /**
     * @param IdentityRepositoryInterface $repository
     * @param SessionInterface $session
     */
    public function __construct(IdentityRepositoryInterface $repository, protected SessionInterface $session, array $config = [])
    {
        parent::__construct($repository, $config);
    }

    /**
     * @return mixed
     */
    public function getCredentials(): mixed
    {
        return $this->session->get($this->config['name']);
    }
}