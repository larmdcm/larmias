<?php

declare(strict_types=1);

namespace Larmias\Session;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ContextInterface;
use Larmias\Contracts\SessionInterface;

class SessionProxy implements SessionInterface
{
    public function __construct(protected ContainerInterface $container, protected ContextInterface $context)
    {
    }

    public function start(): bool
    {
        return $this->getSession()->start();
    }

    public function save(): bool
    {
        return $this->getSession()->save();
    }

    public function all(): array
    {
        return $this->getSession()->all();
    }

    public function set(string $name, mixed $value): bool
    {
        return $this->getSession()->set($name, $value);
    }

    public function get(string $name, mixed $default = null): mixed
    {
        return $this->getSession()->get($name, $default);
    }

    public function pull(string $name): mixed
    {
        return $this->getSession()->pull($name);
    }

    public function push(string $name, mixed $value): bool
    {
        return $this->getSession()->push($name, $value);
    }

    public function has(string $name): bool
    {
        return $this->getSession()->has($name);
    }

    public function delete(string $name): bool
    {
        return $this->getSession()->delete($name);
    }

    public function clear(): bool
    {
        return $this->getSession()->clear();
    }

    public function destroy(): void
    {
        $this->getSession()->destroy();
    }

    public function validId(?string $id = null): bool
    {
        return $this->getSession()->validId($id);
    }

    public function generateSessionId(): string
    {
        return $this->getSession()->generateSessionId();
    }

    public function regenerate(bool $destroy = false): void
    {
        $this->getSession()->regenerate($destroy);
    }

    public function isStarted(): bool
    {
        return $this->getSession()->isStarted();
    }

    public function setData(array $data): void
    {
        $this->getSession()->setData($data);
    }

    public function setId(string $id): void
    {
        $this->getSession()->setId($id);
    }

    public function setName(string $name): void
    {
        $this->getSession()->setName($name);
    }

    public function getName(): string
    {
        return $this->getSession()->getName();
    }

    public function getId(): string
    {
        return $this->getSession()->getId();
    }

    /**
     * @return SessionInterface
     */
    protected function getSession(): SessionInterface
    {
        return $this->context->remember(SessionInterface::class, function () {
            return $this->container->make(Session::class, [], true);
        });
    }
}