<?php

declare(strict_types=1);

return [
    'phar_file' => '/bin/larmias.phar',
    'main_file' => 'bin/larmias.php',
    'phar_alias' => 'larmias',
    'build_dir' => '',
    'exclude_pattern' => '#^(?!.*(composer.json|/.github/|/.idea/|/.git/|/.setting/|/runtime/|/vendor-bin/|/build/))(.*)$#',
    'exclude_files' => [
        '.env', 'LICENSE', 'composer.json', 'composer.lock', 'larmias.phar'
    ],
];