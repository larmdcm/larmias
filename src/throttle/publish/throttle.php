<?php

declare(strict_types=1);

return [
    // 限流算法驱动
    'driver' => \Larmias\Throttle\Drivers\CounterSlider::class,
    // 键前缀
    'prefix' => 'throttle:',
];