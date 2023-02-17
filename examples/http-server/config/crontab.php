<?php
declare(strict_types=1);

use Larmias\Crontab\Crontab;

return [
    'enable' => true,
    'crontab' => [
        new Crontab('1 * * * *', function () {
             echo 'crontab call.' . PHP_EOL;
        })
    ]
];
