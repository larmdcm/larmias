<?php

declare(strict_types=1);

namespace Larmias\ShareMemory\Command;

use Larmias\ShareMemory\Contracts\AuthInterface;

class AuthCommand extends Command
{
    public function handle(): string
    {
        /** @var AuthInterface $auth */
        $auth = $this->container->get(AuthInterface::class);
        $auth->login($this->command->args);
        return 'ok';
    }
}