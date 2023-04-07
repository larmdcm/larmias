<?php

declare(strict_types=1);

namespace Larmias\Database\Model\Concerns;

use function md5;
use function uniqid;
use function mt_rand;
use function substr;

trait HasUuid
{
    /**
     * @return string
     */
    public function generateUniqueId(): string
    {
        $chars = md5(uniqid((string)mt_rand(), true));
        return substr($chars, 0, 8) . '-'
            . substr($chars, 8, 4) . '-'
            . substr($chars, 12, 4) . '-'
            . substr($chars, 16, 4) . '-'
            . substr($chars, 20, 12);
    }
}