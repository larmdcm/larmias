<?php

declare(strict_types=1);

namespace Larmias\Validation;

use Larmias\Contracts\ValidatorInterface;
use Closure;
use Larmias\Utils\Str;

class Validator implements ValidatorInterface
{
    /**
     * @var array
     */
    protected array $attributes = [];

    /**
     * @var array
     */
    protected array $scenes = [];

    /**
     * @var array
     */
    protected array $errors = [];

    /**
     * @var bool
     */
    protected bool $failException = true;

    /**
     * @var bool
     */
    protected bool $batch = false;

    /**
     * @var array
     */
    protected array $validateHandlers = [];

    /**
     * @var array
     */
    protected array $validateMessages = [];

    /**
     * @var string|null
     */
    protected ?string $currentScene = null;

    /**
     * @var array
     */
    protected static array $maker = [];

    /**
     * Validator constructor.
     *
     * @param array $rules
     * @param array $messages
     */
    public function __construct(protected array $rules = [], protected array $messages = [])
    {
        foreach (static::$maker as $maker) {
            $maker($this);
        }
    }

    /**
     * @param Closure $maker
     * @return void
     */
    public static function maker(Closure $maker)
    {
        static::$maker[] = $maker;
    }

    /**
     * @param array $rules
     * @param array $messages
     * @return \Larmias\Validation\Validator
     */
    public static function make(array $rules = [], array $messages = []): Validator
    {
        return new static($rules, $messages);
    }

    public function check(): bool
    {
        return true;
    }

    /**
     * @param string $rule
     * @param callable $handler
     * @param string|null $message
     * @return self
     */
    public function extend(string $rule, callable $handler, ?string $message = null): self
    {
        $this->validateHandlers[$rule] = $handler;
        if ($message) {
            $this->validateMessages[$rule] = $message;
        }
        return $this;
    }

    /**
     * @param string|array $rule
     * @param string $message
     * @return self
     */
    public function validateMessage(string|array $rule, string $message): self
    {
        if (\is_array($rule)) {
            $this->validateMessages = \array_merge($this->validateMessages, $rule);
        } else {
            $this->validateMessages[$rule] = $message;
        }
        return $this;
    }

    /**
     * @param string|array $field
     * @param string|array|null $attribute
     * @return self
     */
    public function rule(string|array $field, string|array $attribute = null): self
    {
        if (\is_array($field)) {
            $this->rules = \array_merge($this->rules, $field);
            if (\is_array($attribute)) {
                $this->attribute($attribute);
            }
        } else {
            $this->rules[$field] = $attribute;
        }
        return $this;
    }

    /**
     * @param array $attributes
     * @return self
     */
    public function attribute(array $attributes): self
    {
        $this->attributes = \array_merge($this->attributes, $attributes);
        return $this;
    }

    /**
     * @param array $messages
     * @return self
     */
    public function message(array $messages): self
    {
        $this->messages = \array_merge($this->messages, $messages);
        return $this;
    }

    /**
     * 设置错误是否抛出异常
     *
     * @param bool $failException
     * @return self
     */
    public function failException(bool $failException): self
    {
        $this->failException = $failException;
        return $this;
    }

    /**
     * @param string $scene
     * @return self
     */
    public function scene(string $scene): self
    {
        $this->currentScene = $scene;
        return $this;
    }

    /**
     * @param array $scenes
     * @return self
     */
    public function scenes(array $scenes): self
    {
        $this->scenes = \array_merge($this->scenes, $scenes);
        return $this;
    }

    /**
     * @param string $scene
     * @return bool
     */
    public function hasScene(string $scene): bool
    {
        return isset($this->scenes[$scene]) || \method_exists($this, 'scene' . Str::studly($scene));
    }

    /**
     * @param string $scene
     * @return array
     */
    public function getScene(string $scene): array
    {
        return [];
    }

    /**
     * 设置是否批量验证
     *
     * @param bool $batch
     * @return self
     */
    public function batch(bool $batch): self
    {
        $this->batch = $batch;
        return $this;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}