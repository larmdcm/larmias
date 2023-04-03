<?php

declare(strict_types=1);

namespace Larmias\SharedMemory;

use Larmias\Contracts\Worker\WorkerInterface;
use Larmias\SharedMemory\Contracts\AuthInterface;
use Larmias\SharedMemory\Exceptions\AuthenticateException;
use Larmias\SharedMemory\Message\Command;

class Auth implements AuthInterface
{
    /**
     * @var string
     */
    public const KEY_AUTH = 'auth';

    /**
     * @var string|null
     */
    protected ?string $password = null;

    /**
     * @param WorkerInterface $worker
     */
    public function __construct(protected WorkerInterface $worker)
    {
        $this->password = $this->worker->getSettings('auth_password');
    }

    /**
     * @param array $params
     * @param bool $throwException
     * @return bool
     */
    public function login(array $params, bool $throwException = true): bool
    {
        $password = $params[0] ?? '';
        if ($password && $password === $this->password) {
            Context::setData(self::KEY_AUTH, true);
            return true;
        }
        return $throwException ? throw new AuthenticateException('Authentication failed') : false;
    }

    /**
     * @param Command $command
     * @param bool $throwException
     * @return bool
     */
    public function check(Command $command, bool $throwException = true): bool
    {
        if (!$this->password || \in_array($command->name, [Command::COMMAND_PING, Command::COMMAND_AUTH])) {
            return true;
        }
        if (!Context::getData(self::KEY_AUTH)) {
            return $throwException ? throw new AuthenticateException('Authentication failed') : false;
        }
        return true;
    }
}