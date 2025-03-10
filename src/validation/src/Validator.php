<?php

declare(strict_types=1);

namespace Larmias\Validation;

use Larmias\Contracts\TranslatorInterface;
use Larmias\Collection\Arr;
use Larmias\Validation\Concerns\ValidateRules;
use Larmias\Validation\Exceptions\ValidateException;
use Larmias\Validation\Exceptions\RuleException;
use Larmias\Contracts\ValidatorInterface;
use Larmias\Stringable\Str;
use Closure;
use function array_unshift;
use function array_merge;
use function in_array;
use function is_array;
use function is_string;
use function explode;
use function method_exists;
use function call_user_func;
use function call_user_func_array;

class Validator implements ValidatorInterface
{
    use ValidateRules;

    /**
     * @var array
     */
    protected static array $maker = [];

    /**
     * @var Rules[]
     */
    protected array $rules = [];

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
     * @var array|null
     */
    protected ?array $validateData = null;

    /**
     * @var callable[]
     */
    protected array $validateHandlers = [];

    /**
     * @var array
     */
    protected array $validateMessages = [
        'required' => ':attribute required',
        'number' => ':attribute must be numeric',
        'integer' => ':attribute must be integer',
        'float' => ':attribute must be float',
        'boolean' => ':attribute must be bool',
        'array' => ':attribute must be a array',
        'max' => 'max size of :attribute must be :rule',
        'min' => 'min size of :attribute must be :rule',
        'in' => ':attribute must be in :rule',
        'notIn' => ':attribute be not in :rule',
        'between' => ':attribute must between :0 - :1',
        'notBetween' => ':attribute not between :0 - :1',
        'length' => 'size of :attribute must be :rule',
        'confirm' => ':attribute out of accord with :2',
        'different' => ':attribute cannot be same with :2',
        'egt' => ':attribute must greater than or equal :rule',
        'gt' => ':attribute must greater than :rule',
        'elt' => ':attribute must less than or equal :rule',
        'lt' => ':attribute must less than :rule',
        'eq' => ':attribute must equal :rule',
        'accepted' => ':attribute must be yes,on or 1',
        'date' => ':attribute not a valid datetime',
        'dateFormat' => ':attribute must be dateFormat of :rule',
        'email' => ':attribute not a valid email address',
        'mobile' => ':attribute not a valid mobile',
        'idCard' => ':attribute not a valid idCard',
        'url' => ':attribute not a valid url',
        'activeUrl' => ':attribute not a valid activeUrl',
        'ip' => ':attribute not a valid ip',
        'zip' => ':attribute not a valid zip',
        'macAddr' => ':attribute not a valid macAddr',
        'regex' => ':attribute not a valid regex',
        'file' => ':attribute not a valid file',
        'image' => ':attribute not a valid image',
        'fileMime' => ':attribute not a valid fileMime',
        'fileExt' => ':attribute not a valid fileExt',
        'default_message' => ':attribute verification failed',
    ];

    /**
     * @var string|null
     */
    protected ?string $currentScene = null;

    /**
     * @var array
     */
    protected array $only = [];

    /**
     * @var array
     */
    protected array $except = [];

    /**
     * @var array
     */
    protected array $append = [];

    /**
     * @var array
     */
    protected array $remove = [];

    /**
     * @var TranslatorInterface
     */
    protected TranslatorInterface $translator;

    /**
     * Validator constructor.
     *
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param array $attributes
     */
    public function __construct(protected array $data = [], array $rules = [], protected array $messages = [], protected array $attributes = [])
    {
        $this->rule($rules);

        foreach (static::$maker as $maker) {
            $maker($this);
        }
    }

    /**
     * @param Closure $maker
     * @return void
     */
    public static function maker(Closure $maker): void
    {
        static::$maker[] = $maker;
    }

    /**
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param array $attributes
     * @return Validator
     */
    public static function make(array $data = [], array $rules = [], array $messages = [], array $attributes = []): Validator
    {
        return new static($data, $rules, $messages, $attributes);
    }

    /**
     * @param TranslatorInterface $translator
     * @return self
     */
    public function setTranslator(TranslatorInterface $translator): self
    {
        $this->translator = $translator;
        return $this;
    }

    /**
     * @return array
     */
    public function validated(): array
    {
        if ($this->validateData === null) {
            $this->fails();
        }
        return $this->validateData;
    }

    /**
     * 验证
     * @return bool
     */
    public function fails(): bool
    {
        $errors = [];
        $checkEnd = false;
        $validateData = [];
        if ($this->currentScene && $this->hasScene($this->currentScene)) {
            call_user_func($this->getScene($this->currentScene));
        }
        $rules = $this->rules;
        foreach ($rules as $field => $ruleItems) {
            if ($checkEnd) {
                break;
            }
            $value = $this->getDataValue($field);
            Arr::set($validateData, $field, $value);
            if (!empty($this->only) && !in_array($field, $this->only)) {
                continue;
            }
            if (!empty($this->except) && in_array($field, $this->except)) {
                continue;
            }
            if (isset($this->append[$field])) {
                $ruleItems->merge($this->append[$field]);
            }

            $hasRequired = $ruleItems->has('required');

            /** @var Rule $ruleItem */
            foreach ($ruleItems as $ruleItem) {
                if ($ruleItem->getName() !== 'required' && !$hasRequired && !ValidateUtils::isRequired($value)) {
                    continue;
                }
                $check = $this->checkRule($ruleItem, $field, $value);
                if (!$check) {
                    $errors[$field][] = $this->getValidateMessage($ruleItem, $field);
                    if (Arr::has($validateData, $field)) {
                        Arr::forget($validateData, $field);
                    }
                    if (!$this->batch) {
                        $checkEnd = true;
                    }
                }
                if ($checkEnd) {
                    break;
                }
            }
        }
        $this->errors = $errors;
        $this->validateData = $validateData;
        $result = empty($this->errors);
        if (!$result && $this->failException) {
            throw new ValidateException($this->errors);
        }
        return $result;
    }

    /**
     * @param Rule $ruleItem
     * @param string $field
     * @param mixed $value
     * @return bool
     */
    protected function checkRule(Rule $ruleItem, string $field, mixed $value): bool
    {
        $name = $ruleItem->getName();
        $args = $ruleItem->getArgs();

        if (isset($this->remove[$field])) {
            $removeRules = $this->remove[$field];
            if (in_array($name, $removeRules)) {
                return true;
            }
        }

        if ($args instanceof Closure) {
            return $args($value);
        }

        $method = 'validate' . Str::studly($name);
        array_unshift($args, $value);
        array_unshift($args, $field);

        if (isset($this->validateHandlers[$name])) {
            return call_user_func_array($this->validateHandlers[$name], $args);
        }

        if (method_exists($this, $method)) {
            return call_user_func_array([$this, $method], $args);
        }

        throw new RuleException('rule is not exists:' . $name, $ruleItem);
    }

    /**
     * @param Rule $ruleItem
     * @param string $field
     * @return string
     */
    protected function getValidateMessage(Rule $ruleItem, string $field): string
    {
        $message = $this->messages[$field . '.' . $ruleItem->getName()] ?? ($this->validateMessages[$ruleItem->getName()] ?? $this->validateMessages['default_message']);
        $args = $ruleItem->getArgs() instanceof Closure ? [] : $ruleItem->getArgs();
        return Str::template($this->trans($message), [
            ...$args,
            'field' => $field,
            'rule' => $ruleItem->getName(),
            'attribute' => $this->attributes[$field] ?? $field,
        ], ['open' => ':', 'close' => '']);
    }

    /**
     * @param string $message
     * @return string
     */
    protected function trans(string $message): string
    {
        if (isset($this->translator)) {
            $messages = [$message, 'validator.' . $message];
            foreach ($messages as $item) {
                if ($this->translator->has($item)) {
                    return $this->translator->trans($item);
                }
            }
        }
        return $message;
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
        if (is_array($rule)) {
            $this->validateMessages = array_merge($this->validateMessages, $rule);
        } else {
            $this->validateMessages[$rule] = $message;
        }
        return $this;
    }

    /**
     * @param array $data
     * @return self
     */
    public function data(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param string|array $field
     * @param string|array $rule
     * @return self
     */
    public function rule(string|array $field, string|array $rule = []): self
    {
        return $this->setRule('rules', $field, $rule);
    }

    /**
     * @param string $property
     * @param string|array $field
     * @param string|array $rule
     * @return self
     */
    protected function setRule(string $property, string|array $field, string|array $rule = []): self
    {
        if (is_array($field)) {
            foreach ($field as $key => $val) {
                $this->setRule($property, $key, $val);
            }
        } else {
            $this->{$property}[$field] = new Rules($rule);
        }
        return $this;
    }

    /**
     * @param array $attributes
     * @return self
     */
    public function attribute(array $attributes): self
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    /**
     * @param array $messages
     * @return self
     */
    public function message(array $messages): self
    {
        $this->messages = array_merge($this->messages, $messages);
        return $this;
    }

    /**
     * @param array $only
     * @return self
     */
    public function only(array $only): self
    {
        $this->only = $only;
        return $this;
    }

    /**
     * @param array $except
     * @return self
     */
    public function except(array $except): self
    {
        $this->except = $except;
        return $this;
    }

    /**
     * @param string|array $field
     * @param string|array $rule
     * @return self
     */
    public function append(string|array $field, string|array $rule = []): self
    {
        return $this->setRule('append', $field, $rule);
    }

    /**
     * @param string|array $field
     * @param string|array $rule
     * @return self
     */
    public function remove(string|array $field, string|array $rule = []): self
    {
        if (is_array($field)) {
            foreach ($field as $key => $value) {
                $this->remove($key, $value);
            }
        } else {
            $this->remove[$field] = is_string($rule) ? explode('|', $rule) : $rule;
        }
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
        $this->scenes = array_merge($this->scenes, $scenes);
        return $this;
    }

    /**
     * @param string $scene
     * @return bool
     */
    public function hasScene(string $scene): bool
    {
        return isset($this->scenes[$scene]) || method_exists($this, 'scene' . Str::studly($scene));
    }

    /**
     * @param string $scene
     * @return Closure
     */
    public function getScene(string $scene): Closure
    {
        return function () use ($scene) {
            $item = $this->scenes[$scene] ?? null;
            if ($item !== null) {
                if ($item instanceof Closure) {
                    $item($this);
                } else if (is_array($item)) {
                    $this->only = $item;
                }
            } else {
                call_user_func([$this, 'scene' . Str::studly($scene)]);
            }
        };
    }

    /**
     * 判断字段规则是否存在
     * @param string $field
     * @param string|array $rules
     * @return bool
     */
    public function hasRule(string $field, string|array $rules): bool
    {
        return isset($this->rules[$field]) && $this->rules[$field]->has($rules);
    }

    /**
     * 设置是否批量验证
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
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * @param string $field
     * @return mixed
     */
    protected function getDataValue(string $field): mixed
    {
        return Arr::get($this->data, $field);
    }
}