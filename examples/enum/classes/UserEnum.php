<?php

namespace Enum;

use Larmias\Enum\AbstractEnum;
use Larmias\Enum\Annotation\Text;

class UserEnum extends AbstractEnum
{
    #[Text('开启')]
    public const STATUS_ENABLE = 1;

    #[Text('禁用')]
    public const STATUS_DISABLE = 2;

    public static function data(): array
    {
        return [
            self::STATUS_ENABLE => [
                'label' => '状态',
            ],
            self::STATUS_DISABLE => [
                'label' => '状态',
            ],
        ];
    }
}