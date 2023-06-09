<?php

namespace Constants;

use Larmias\Constants\AbstractConstants;
use Larmias\Constants\Annotation\Dict;
use Larmias\Constants\Annotation\Text;

class Constants extends AbstractConstants
{
    #[Text('开启')]
    #[Dict(['label' => '状态'])]
    public const STATUS_ENABLE = 1;

    #[Text('禁用')]
    #[Dict(['label' => '状态'])]
    public const STATUS_DISABLE = 2;
}