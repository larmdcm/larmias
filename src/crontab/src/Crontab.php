<?php

declare(strict_types=1);

namespace Larmias\Crontab;

use Carbon\Carbon;
use JsonSerializable;
use Opis\Closure\SerializableClosure;

class Crontab implements JsonSerializable
{
    /**
     * @var bool
     */
    protected bool $singleton = false;

    /**
     * @var bool
     */
    protected bool $onOneServer = false;

    /**
     * @var string
     */
    protected string $mutexName;

    /**
     * @var int
     */
    protected int $mutexExpires = 3600;

    /**
     * @var Carbon
     */
    protected Carbon $executeTime;

    /**
     * @var bool
     */
    protected bool $enable = true;

    /**
     * @param string $rule
     * @param mixed $handler
     * @param string $name
     */
    public function __construct(protected string $rule = '', protected mixed $handler = null, protected string $name = '')
    {
        if (!$this->name) {
            $this->name = static::class;
        }
        $this->mutexName = 'mutex:' . $this->name;
    }

    /**
     * @param array $data
     * @return Crontab
     */
    public static function parse(array $data): Crontab
    {
        $crontab = new static();
        $crontab->setName($data['name']);
        $crontab->setRule($data['rule']);
        $handler = $data['handler'];
        if (\is_array($handler) && isset($handler['type'])) {
            if ($handler['type'] === 'closure') {
                $handler = \unserialize($handler['handler']);
                $handler = $handler->getClosure();
            } else {
                $handler = $handler['handler'];
            }
        }
        $crontab->setHandler($handler);
        $crontab->setExecuteTime(\unserialize($data['executeTime']));
        $crontab->setEnable($data['enable']);
        $crontab->setMutexExpires($data['mutexExpires']);
        $crontab->setMutexName($data['mutexName']);
        $crontab->setOnOneServer($data['onOneServer']);
        $crontab->setSingleton($data['singleton']);
        return $crontab;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getRule(): string
    {
        return $this->rule;
    }

    /**
     * @param string $rule
     * @return self
     */
    public function setRule(string $rule): self
    {
        $this->rule = $rule;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHandler(): mixed
    {
        return $this->handler;
    }

    /**
     * @param mixed $handler
     * @return self
     */
    public function setHandler(mixed $handler): self
    {
        $this->handler = $handler;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSingleton(): bool
    {
        return $this->singleton;
    }

    /**
     * @param bool $singleton
     * @return self
     */
    public function setSingleton(bool $singleton): self
    {
        $this->singleton = $singleton;
        return $this;
    }

    /**
     * @return bool
     */
    public function isOnOneServer(): bool
    {
        return $this->onOneServer;
    }

    /**
     * @param bool $onOneServer
     * @return self
     */
    public function setOnOneServer(bool $onOneServer): self
    {
        $this->onOneServer = $onOneServer;
        return $this;
    }

    /**
     * @return string
     */
    public function getMutexName(): string
    {
        return $this->mutexName;
    }

    /**
     * @param string $mutexName
     * @return self
     */
    public function setMutexName(string $mutexName): self
    {
        $this->mutexName = $mutexName;
        return $this;
    }

    /**
     * @return int
     */
    public function getMutexExpires(): int
    {
        return $this->mutexExpires;
    }

    /**
     * @param int $mutexExpires
     * @return self
     */
    public function setMutexExpires(int $mutexExpires): self
    {
        $this->mutexExpires = $mutexExpires;
        return $this;
    }

    /**
     * @return Carbon
     */
    public function getExecuteTime(): Carbon
    {
        return $this->executeTime;
    }

    /**
     * @param Carbon $executeTime
     * @return self
     */
    public function setExecuteTime(Carbon $executeTime): self
    {
        $this->executeTime = $executeTime;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEnable(): bool
    {
        return $this->enable;
    }

    /**
     * @param bool $enable
     * @return self
     */
    public function setEnable(bool $enable): self
    {
        $this->enable = $enable;
        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        $handler = ['type' => 'mixed', 'handler' => $this->getHandler()];
        if ($handler['handler'] instanceof \Closure) {
            $handler['type'] = 'closure';
            $handler['handler'] = \serialize(new SerializableClosure($handler['handler']));
        }
        return [
            'name' => $this->getName(),
            'rule' => $this->getRule(),
            'handler' => $handler,
            'mutexName' => $this->getMutexName(),
            'mutexExpires' => $this->getMutexExpires(),
            'executeTime' => $this->getExecuteTime()->serialize(),
            'enable' => $this->isEnable(),
            'singleton' => $this->isSingleton(),
            'onOneServer' => $this->isOnOneServer(),
        ];
    }
}