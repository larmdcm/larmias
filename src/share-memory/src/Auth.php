<?php

declare(strict_types=1);

namespace Larmias\ShareMemory;

use Larmias\Engine\Contracts\WorkerInterface;
use Larmias\ShareMemory\Contracts\AuthInterface;
use Larmias\ShareMemory\Exceptions\AuthenticateException;
use Larmias\ShareMemory\Message\Command;

class Auth implements AuthInterface
{
    public const KEY_AUTH = 'auth';

    protected ?string $authPassword = null;

    public function __construct(protected WorkerInterface $worker)
    {
        $this->authPassword = $this->worker->getSettings('auth_password');
    }

    public function login(array $params, bool $throwException = true): bool
    {
        $password = $params[0] ?? '';
        if ($password && $password === $this->authPassword) {
            Context::setData(self::KEY_AUTH, true);
            return true;
        }
        return $throwException ? throw new AuthenticateException('Authentication failed') : false;
    }

    public function check(Command $command, bool $throwException = true): bool
    {
        if (!$this->authPassword || \in_array($command->name, [Command::COMMAND_PING, Command::COMMAND_AUTH])) {
            return true;
        }
        if (!Context::getData(self::KEY_AUTH)) {
            return $throwException ? throw new AuthenticateException('Authentication failed') : false;
        }
        return true;
    }
}