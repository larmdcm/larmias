<?php

declare(strict_types=1);

namespace Larmias\Auth\Authentication;

use Psr\Http\Message\ServerRequestInterface;

class TokenAuthentication extends Authentication
{
    /**
     * @var array
     */
    protected array $config = [
        'auth_name' => 'token',
    ];

    /**
     * @param mixed $parameter
     * @return string
     */
    public function getCredentials(mixed $parameter): string
    {
        /** @var ServerRequestInterface $parameter */
        return $parameter->getHeaderLine($this->config['auth_name']);
    }
}