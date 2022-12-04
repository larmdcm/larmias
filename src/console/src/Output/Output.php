<?php

declare(strict_types=1);

namespace Larmias\Console\Output;

use Larmias\Console\Contracts\OutputHandlerInterface;
use Larmias\Console\Contracts\OutputInterface;
use Larmias\Console\Contracts\OutputTableInterface;
use Larmias\Contracts\ContainerInterface;

class Output implements OutputInterface
{
    /**
     * @param ContainerInterface $container
     * @param OutputHandlerInterface $handler
     */
    public function __construct(protected ContainerInterface $container,protected OutputHandlerInterface $handler)
    {
    }

    /**
     * @param string $style
     * @param string $message
     * @return void
     */
    protected function block(string $style, string $message): void
    {
        $this->writeln("<{$style}>{$message}</$style>");
    }

    /**
     * @param string $message
     * @return void
     */
    public function info(string $message): void
    {
        $this->block(__FUNCTION__,$message);
    }

    /**
     * @param string $message
     * @return void
     */
    public function error(string $message): void
    {
        $this->block(__FUNCTION__,$message);
    }

    /**
     * @param string $message
     * @return void
     */
    public function comment(string $message): void
    {
        $this->block(__FUNCTION__,$message);
    }

    /**
     * @param string $message
     * @return void
     */
    public function question(string $message): void
    {
        $this->block(__FUNCTION__,$message);
    }

    /**
     * @param string $message
     * @return void
     */
    public function highlight(string $message): void
    {
        $this->block(__FUNCTION__,$message);
    }

    /**
     * @param string $message
     * @return void
     */
    public function warning(string $message): void
    {
        $this->block(__FUNCTION__,$message);
    }


    /**
     * 输出空行
     *
     * @param int $count
     * @return void
     */
    public function newLine(int $count = 1): void
    {
        $this->write(str_repeat(PHP_EOL, $count));
    }

    /**
     * 输出信息并换行
     *
     * @param string|array $messages
     * @param int $type
     * @return void
     */
    public function writeln(string|array $messages, int $type = 0): void
    {
        $this->write($messages, true, $type);
    }

    /**
     * @return OutputTableInterface
     */
    public function table(): OutputTableInterface
    {
        /** @var OutputTableInterface $table */
        $table = $this->container->make(OutputTableInterface::class,[],true);
        return $table;
    }

    /**
     * 输出信息
     *
     * @param string|array $messages
     * @param bool $newline
     * @param int $type
     */
    public function write(string|array $messages, bool $newline = false, int $type = 0): void
    {
        $this->handler->write($messages, $newline, $type);
    }
}