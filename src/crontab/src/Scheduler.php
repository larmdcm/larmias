<?php

declare(strict_types=1);

namespace Larmias\Crontab;

use Larmias\Crontab\Contracts\ExecutorInterface;
use Larmias\Crontab\Contracts\ParserInterface;
use Larmias\Crontab\Contracts\SchedulerInterface;
use SplQueue;
use function time;

class Scheduler implements SchedulerInterface
{
    /**
     * @var Crontab[]
     */
    protected array $list = [];

    /**
     * @var SplQueue
     */
    protected SplQueue $queue;

    /**
     * @param ParserInterface $parser
     * @param ExecutorInterface $executor
     */
    public function __construct(protected ParserInterface $parser, protected ExecutorInterface $executor)
    {
        $this->queue = new SplQueue();
    }

    /**
     * @param Crontab $crontab
     * @return self
     */
    public function add(Crontab $crontab): self
    {
        if ($crontab->getHandler() && $this->parser->parse($crontab->getRule())) {
            $this->list[$crontab->getName()] = $crontab;
        }
        return $this;
    }

    /**
     * @param array $list
     * @return self
     */
    public function batch(array $list): self
    {
        foreach ($list as $crontab) {
            $this->add($crontab);
        }
        return $this;
    }

    /**
     * @return void
     */
    public function run(): void
    {
        $queue = $this->schedule();

        while (!$queue->isEmpty()) {
            $crontab = $queue->dequeue();
            $this->executor->execute($crontab);
        }
    }

    /**
     * @return SplQueue
     */
    public function schedule(): SplQueue
    {
        $startTime = time();
        foreach ($this->list as $crontab) {
            $result = $this->parser->parse($crontab->getRule(), $startTime);
            foreach ($result as $time) {
                $this->queue->enqueue(clone $crontab->setExecuteTime($time));
            }
        }
        return $this->queue;
    }
}