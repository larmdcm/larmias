<?php

declare(strict_types=1);

namespace Larmias\Command;

use Larmias\Command\Contracts\ExceptionHandlerInterface;
use Larmias\Command\Events\AfterExecute;
use Larmias\Command\Events\AfterHandle;
use Larmias\Command\Events\BeforeHandle;
use Larmias\Command\Events\FailToHandle;
use Larmias\Command\Exceptions\Handler\ExceptionHandler;
use Larmias\Contracts\ContainerInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\Question;
use Larmias\Utils\Contracts\Arrayable;
use Larmias\Utils\Str;
use Psr\EventDispatcher\EventDispatcherInterface;

abstract class Command extends SymfonyCommand
{
    /**
     * @var string
     */
    protected string $name;

    /**
     * @var string
     */
    protected string $description = '';

    /**
     * @var string
     */
    protected string $help = '';

    /**
     * @var InputInterface
     */
    protected InputInterface $input;

    /**
     * @var SymfonyStyle
     */
    protected SymfonyStyle $output;

    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var EventDispatcherInterface|null
     */
    protected ?EventDispatcherInterface $eventDispatcher = null;

    /**
     * @param string|null $name
     */
    public function __construct(string $name = null)
    {
        if (!$name && $this->name) {
            $name = $this->name;
        }
        parent::__construct($name);
        $this->setDescription($this->description);
        $this->setHelp($this->help);
    }

    /**
     * Handle the current command.
     *
     * @return void
     */
    abstract public function handle(): void;

    /**
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();

        if (\method_exists($this, 'getArguments')) {
            foreach ($this->getArguments() ?? [] as $arguments) {
                \call_user_func_array([$this, 'addArgument'], $arguments);
            }
        }

        if (\method_exists($this, 'getOptions')) {
            foreach ($this->getOptions() ?? [] as $options) {
                \call_user_func_array([$this, 'addOption'], $options);
            }
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws ExceptionInterface
     */
    public function run(InputInterface $input, OutputInterface $output): int
    {
        $this->output = new SymfonyStyle($input, $output);
        $this->input = $input;
        return parent::run($this->input, $this->output);
    }

    /**
     * Confirm a question with the user.
     *
     * @param string $question
     * @param bool $default
     * @return bool
     */
    public function confirm(string $question, bool $default = false): bool
    {
        return $this->output->confirm($question, $default);
    }

    /**
     * Prompt the user for input.
     *
     * @param string $question
     * @param mixed|null $default
     * @return mixed
     */
    public function ask(string $question, mixed $default = null): mixed
    {
        return $this->output->ask($question, $default);
    }

    /**
     * Prompt the user for input with auto-completion.
     *
     * @param string $question
     * @param array $choices
     * @param mixed|null $default
     * @return mixed
     */
    public function anticipate(string $question, array $choices, mixed $default = null): mixed
    {
        return $this->askWithCompletion($question, $choices, $default);
    }

    /**
     * Prompt the user for input with auto-completion.
     *
     * @param string $question
     * @param array $choices
     * @param mixed|null $default
     * @return mixed
     */
    public function askWithCompletion(string $question, array $choices, mixed $default = null): mixed
    {
        $question = new Question($question, $default);

        $question->setAutocompleterValues($choices);

        return $this->output->askQuestion($question);
    }

    /**
     * Prompt the user for input but hide the answer from the console.
     *
     * @param string $question
     * @param bool $fallback
     * @return mixed
     */
    public function secret(string $question, bool $fallback = true): mixed
    {
        $question = new Question($question);

        $question->setHidden(true)->setHiddenFallback($fallback);

        return $this->output->askQuestion($question);
    }

    /**
     * Give the user a multiple choice from an array of answers.
     *
     * @param string $question
     * @param array $choices
     * @param mixed|null $default
     * @param int|null $attempts
     * @return array
     */
    public function choiceMultiple(
        string $question,
        array  $choices,
        mixed  $default = null,
        ?int   $attempts = null
    ): array
    {
        $question = new ChoiceQuestion($question, $choices, $default);

        $question->setMaxAttempts($attempts)->setMultiselect(true);

        return $this->output->askQuestion($question);
    }

    /**
     * Give the user a single choice from an array of answers.
     *
     * @param string $question
     * @param array $choices
     * @param mixed|null $default
     * @param int|null $attempts
     * @return string
     */
    public function choice(
        string $question,
        array  $choices,
        mixed  $default = null,
        ?int   $attempts = null
    ): string
    {
        return $this->choiceMultiple($question, $choices, $default, $attempts)[0];
    }

    /**
     * Format input to textual table.
     *
     * @param mixed $rows
     * @param mixed $tableStyle
     * @return void
     */
    public function table(array $headers, $rows, string|TableStyle $tableStyle = 'default', array $columnStyles = []): void
    {
        $table = new Table($this->output);

        if ($rows instanceof Arrayable) {
            $rows = $rows->toArray();
        }

        $table->setHeaders($headers)->setRows($rows)->setStyle($tableStyle);

        foreach ($columnStyles as $columnIndex => $columnStyle) {
            $table->setColumnStyle($columnIndex, $columnStyle);
        }

        $table->render();
    }

    /**
     * Write a string as standard output.
     *
     * @param string $string
     * @param string $style
     * @param int $verbosity
     * @return void
     */
    public function line(string $string, string $style = '', int $verbosity = OutputInterface::VERBOSITY_NORMAL): void
    {
        $styled = $style ? "<{$style}>{$string}</{$style}>" : $string;
        $this->output->writeln($styled, $verbosity);
    }

    /**
     * Write a string as information output.
     *
     * @param mixed $string
     * @param int $verbosity
     * @return void
     */
    public function info(string $string, int $verbosity = OutputInterface::VERBOSITY_NORMAL): void
    {
        $this->line($string, 'info', $verbosity);
    }

    /**
     * Write a string as comment output.
     *
     * @param mixed $string
     * @param int $verbosity
     * @return void
     */
    public function comment(string $string, int $verbosity = OutputInterface::VERBOSITY_NORMAL): void
    {
        $this->line($string, 'comment', $verbosity);
    }

    /**
     * Write a string as question output.
     *
     * @param mixed $string
     * @param int $verbosity
     * @return void
     */
    public function question(string $string, int $verbosity = OutputInterface::VERBOSITY_NORMAL): void
    {
        $this->line($string, 'question', $verbosity);
    }

    /**
     * Write a string as error output.
     *
     * @param string $string
     * @param int $verbosity
     * @return void
     */
    public function error(string $string, int $verbosity = OutputInterface::VERBOSITY_NORMAL): void
    {
        $this->line($string, 'error', $verbosity);
    }

    /**
     * Write a string as warning output.
     *
     * @param string $string
     * @param int $verbosity
     * @return void
     */
    public function warn(string $string, int $verbosity = OutputInterface::VERBOSITY_NORMAL): void
    {
        if (!$this->output->getFormatter()->hasStyle('warning')) {
            $style = new OutputFormatterStyle('yellow');
            $this->output->getFormatter()->setStyle('warning', $style);
        }
        $this->line($string, 'warning', $verbosity);
    }

    /**
     * Write a string in an alert box.
     *
     * @param string $string
     * @return void
     */
    public function alert(string $string): void
    {
        $length = Str::length(strip_tags($string)) + 12;
        $this->comment(str_repeat('*', $length));
        $this->comment('*     ' . $string . '     *');
        $this->comment(str_repeat('*', $length));
        $this->output->newLine();
    }

    /**
     * Call another console command.
     *
     * @param string $command
     * @param array $arguments
     * @return int
     * @throws ExceptionInterface
     */
    public function call(string $command, array $arguments = []): int
    {
        $arguments['command'] = $command;

        return $this->getApplication()->find($command)->run(new ArrayInput($arguments), $this->output);
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @param ContainerInterface $container
     * @return self
     */
    public function setContainer(ContainerInterface $container): self
    {
        $this->container = $container;
        return $this;
    }

    /**
     * @return EventDispatcherInterface|null
     */
    public function getEventDispatcher(): ?EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    /**
     * @param EventDispatcherInterface|null $eventDispatcher
     * @return self
     */
    public function setEventDispatcher(?EventDispatcherInterface $eventDispatcher): self
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->eventDispatcher && $this->eventDispatcher->dispatch(new BeforeHandle($this));
            $this->container->invoke([$this, 'handle']);
            $this->eventDispatcher && $this->eventDispatcher->dispatch(new AfterHandle($this));
        } catch (\Throwable $e) {
            $this->eventDispatcher && $this->eventDispatcher->dispatch(new FailToHandle($this, $e));
            /** @var ExceptionHandlerInterface $exceptionHandler */
            $exceptionHandler = $this->container->get(ExceptionHandler::class);
            $exceptionHandler->report($e);
            $exceptionHandler->render($this, $e);
            return $e->getCode();
        } finally {
            $this->eventDispatcher && $this->eventDispatcher->dispatch(new AfterExecute($this));
        }
        return 0;
    }
}