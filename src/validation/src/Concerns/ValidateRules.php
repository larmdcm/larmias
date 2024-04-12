<?php

declare(strict_types=1);

namespace Larmias\Validation\Concerns;

use Larmias\Validation\ValidateUtils;
use Larmias\Validation\Validator;

/**
 * @mixin Validator
 */
trait ValidateRules
{
    /**
     * 验证字段指是否为空
     * @param string $field
     * @param mixed $value
     * @return bool
     */
    protected function validateRequired(string $field, mixed $value): bool
    {
        return ValidateUtils::isRequired($value);
    }

    /**
     * 验证字段是否为数值
     * @param string $field
     * @param mixed $value
     * @return bool
     */
    protected function validateNumber(string $field, mixed $value): bool
    {
        return ValidateUtils::isNumber($value);
    }

    /**
     * 验证字段是否为整数
     * @param string $field
     * @param mixed $value
     * @return bool
     */
    protected function validateInteger(string $field, mixed $value): bool
    {
        return ValidateUtils::isInteger($value);
    }

    /**
     * 验证字段是否为浮点数
     * @param string $field
     * @param mixed $value
     * @return bool
     */
    protected function validateFloat(string $field, mixed $value): bool
    {
        return ValidateUtils::isFloat($value);
    }

    /**
     * 验证字段是否为布尔值
     * @param string $field
     * @param mixed $value
     * @return bool
     */
    protected function validateBoolean(string $field, mixed $value): bool
    {
        return ValidateUtils::isBoolean($value);
    }

    /**
     * 验证字段是否为数组
     * @param string $field
     * @param mixed $value
     * @return bool
     */
    protected function validateArray(string $field, mixed $value): bool
    {
        return ValidateUtils::isArray($value);
    }

    /**
     * 验证字段值是否不超过指定值
     * @param string $field
     * @param mixed $value
     * @param mixed $maxValue
     * @return bool
     */
    protected function validateMax(string $field, mixed $value, mixed $maxValue): bool
    {
        return ValidateUtils::max($this->getSize($field, $value), $maxValue);
    }

    /**
     * 验证字段值是否不小于指定值
     * @param string $field
     * @param mixed $value
     * @param mixed $minValue
     * @return bool
     */
    protected function validateMin(string $field, mixed $value, mixed $minValue): bool
    {
        return ValidateUtils::min($this->getSize($field, $value), $minValue);
    }

    /**
     * 验证字段值是否存在指定值中
     * @param string $field
     * @param mixed $value
     * @param ...$args
     * @return bool
     */
    protected function validateIn(string $field, mixed $value, ...$args): bool
    {
        return ValidateUtils::in($value, ...$args);
    }

    /**
     * 验证字段值是否不存在指定值中
     * @param string $field
     * @param mixed $value
     * @param ...$args
     * @return bool
     */
    protected function validateNotIn(string $field, mixed $value, ...$args): bool
    {
        return !$this->validateIn($field, $value, ...$args);
    }

    /**
     * 验证字段值是否在指定区间中
     * @param string $field
     * @param mixed $value
     * @param ...$args
     * @return bool
     */
    protected function validateBetween(string $field, mixed $value, ...$args): bool
    {
        return ValidateUtils::between($this->getSize($field, $value), $args[0], $args[1]);
    }

    /**
     * 验证字段值是否不在指定区间中
     * @param string $field
     * @param mixed $value
     * @param ...$args
     * @return bool
     */
    protected function validateNotBetween(string $field, mixed $value, ...$args): bool
    {
        return !$this->validateBetween($field, $value, ...$args);
    }

    /**
     * 验证字段值长度是否相等
     * @param string $field
     * @param mixed $value
     * @param ...$args
     * @return bool
     */
    protected function validateLength(string $field, mixed $value, ...$args): bool
    {
        return ValidateUtils::length($this->getSize($field, $value), $args[0]);
    }

    /**
     * 验证字段值是否相等
     * @param string $field
     * @param mixed $value
     * @param string|null $rule
     * @return bool
     */
    protected function validateConfirm(string $field, mixed $value, ?string $rule = null): bool
    {
        if ($rule === null) {
            $rule = $field . '_confirm';
        }

        return $value === $this->getDataValue($rule);
    }

    /**
     * 验证字段值是否不相等
     * @param string $field
     * @param mixed $value
     * @param string|null $rule
     * @return bool
     */
    protected function validateDifferent(string $field, mixed $value, ?string $rule = null): bool
    {
        if ($rule === null) {
            $rule = $field . '_different';
        }

        return $value !== $this->getDataValue($rule);
    }

    /**
     * 验证字段值egt
     * @param string $field
     * @param mixed $value
     * @param ...$args
     * @return bool
     */
    protected function validateEgt(string $field, mixed $value, ...$args): bool
    {
        return ValidateUtils::compare($value, $args[0], '>=');
    }

    /**
     * 验证字段值gt
     * @param string $field
     * @param mixed $value
     * @param ...$args
     * @return bool
     */
    protected function validateGt(string $field, mixed $value, ...$args): bool
    {
        return ValidateUtils::compare($value, $args[0], '>');
    }

    /**
     * 验证字段值elt
     * @param string $field
     * @param mixed $value
     * @param ...$args
     * @return bool
     */
    protected function validateElt(string $field, mixed $value, ...$args): bool
    {
        return ValidateUtils::compare($value, $args[0], '<=');
    }

    /**
     * 验证字段值lt
     * @param string $field
     * @param mixed $value
     * @param ...$args
     * @return bool
     */
    protected function validateLt(string $field, mixed $value, ...$args): bool
    {
        return ValidateUtils::compare($value, $args[0], '<');
    }

    /**
     * 验证字段值eq
     * @param string $field
     * @param mixed $value
     * @param ...$args
     * @return bool
     */
    protected function validateEq(string $field, mixed $value, ...$args): bool
    {
        return ValidateUtils::compare($value, $args[0]);
    }

    /**
     * 验证字段是否为接受标识
     * @param string $field
     * @param mixed $value
     * @return bool
     */
    protected function validateAccepted(string $field, mixed $value): bool
    {
        return ValidateUtils::isAccepted($value);
    }

    /**
     * 验证字段是否是有效的日期
     * @param string $field
     * @param mixed $value
     * @return bool
     */
    protected function validateDate(string $field, mixed $value): bool
    {
        return ValidateUtils::isDate($value);
    }

    /**
     * 验证字段是否是有效的日期
     * @param string $field
     * @param mixed $value
     * @param ...$args
     * @return bool
     */
    protected function validateDateFormat(string $field, mixed $value, ...$args): bool
    {
        return ValidateUtils::dateFormat($value, $args[0]);
    }

    /**
     * 验证字段是否为邮箱
     * @param string $field
     * @param mixed $value
     * @return bool
     */
    protected function validateEmail(string $field, mixed $value): bool
    {
        return ValidateUtils::isEmail($value);
    }

    /**
     * 验证字段是否为手机号码
     * @param string $field
     * @param mixed $value
     * @return bool
     */
    protected function validateMobile(string $field, mixed $value): bool
    {
        return ValidateUtils::isMobile($value);
    }

    /**
     * 验证字段是否为身份证号
     * @param string $field
     * @param mixed $value
     * @return bool
     */
    protected function validateIdCard(string $field, mixed $value): bool
    {
        return ValidateUtils::isIdCard($value);
    }

    /**
     * 验证字段是否为URL
     * @param string $field
     * @param mixed $value
     * @return bool
     */
    protected function validateUrl(string $field, mixed $value): bool
    {
        return ValidateUtils::isUrl($value);
    }

    /**
     * 验证字段是否为IP
     * @param string $field
     * @param mixed $value
     * @return bool
     */
    protected function validateIp(string $field, mixed $value): bool
    {
        return ValidateUtils::isIp($value);
    }

    /**
     * 验证字段是否为邮编
     * @param string $field
     * @param mixed $value
     * @return bool
     */
    protected function validateZip(string $field, mixed $value): bool
    {
        return ValidateUtils::isZip($value);
    }

    /**
     * 验证字段是否为MAC ADDR
     * @param string $field
     * @param mixed $value
     * @return bool
     */
    protected function validateMacAddr(string $field, mixed $value): bool
    {
        return ValidateUtils::isMacAddr($value);
    }

    /**
     * 正则表达式验证字段是否正确
     * @param string $field
     * @param mixed $value
     * @param ...$args
     * @return bool
     */
    protected function validateRegex(string $field, mixed $value, ...$args): bool
    {
        return ValidateUtils::regex($value, ...$args);
    }

    /**
     * 验证字段是否为有效的URL
     * @param string $field
     * @param mixed $value
     * @param ...$args
     * @return bool
     */
    protected function validateActiveUrl(string $field, mixed $value, ...$args): bool
    {
        return ValidateUtils::isActiveUrl($value);
    }

    /**
     * 验证字段是否为文件
     * @param string $field
     * @param mixed $value
     * @param ...$args
     * @return bool
     */
    protected function validateFile(string $field, mixed $value, ...$args): bool
    {
        return ValidateUtils::isFile($value);
    }

    /**
     * 验证字段是否为图片
     * @param string $field
     * @param mixed $value
     * @param ...$args
     * @return bool
     */
    protected function validateImage(string $field, mixed $value, ...$args): bool
    {
        return ValidateUtils::isImage($value);
    }

    /**
     * 验证字段文件MIME
     * @param string $field
     * @param mixed $value
     * @param ...$args
     * @return bool
     */
    protected function validateFileMime(string $field, mixed $value, ...$args): bool
    {
        return ValidateUtils::checkFileMime($value, ...$args);
    }

    /**
     * 验证字段文件后缀
     * @param string $field
     * @param mixed $value
     * @param ...$args
     * @return bool
     */
    protected function validateFileExt(string $field, mixed $value, ...$args): bool
    {
        return ValidateUtils::checkFileExt($value, ...$args);
    }

    /**
     * 获取验证的字段长度
     * @param string $field
     * @param mixed $value
     * @return int|float
     */
    protected function getSize(string $field, mixed $value): int|float
    {
        return ValidateUtils::getSize($value, $this->hasRule($field, ['number', 'integer', 'float']));
    }
}