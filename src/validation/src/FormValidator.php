<?php

declare(strict_types=1);

namespace Larmias\Validation;

use Larmias\Validation\Contracts\FormValidatorInterface;

abstract class FormValidator extends Validator implements FormValidatorInterface
{
    /**
     * @param array $data
     * @return bool
     */
    public function check(array $data): bool
    {
        return $this->data($data)->fails();
    }

    /**
     * @return bool
     */
    public function fails(): bool
    {
        $this->rule($this->getRules())
            ->message($this->getMessages())
            ->attribute($this->getAttributes());
        return parent::fails();
    }

    /**
     * 获取验证规则
     * @return array
     */
    abstract public function getRules(): array;

    /**
     * 获取错误消息
     * @return array
     */
    abstract public function getMessages(): array;

    /**
     * 获取字段属性
     * @return array
     */
    abstract public function getAttributes(): array;
}