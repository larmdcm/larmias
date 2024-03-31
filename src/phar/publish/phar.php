<?php

declare(strict_types=1);

return [
    'build_dir' => '',
    'phar_file' => '${build_dir}/larmias.phar',
    'phar_alias' => 'larmias',
    'main_file' => 'bin/larmias.php',
    'exclude_pattern' => '#^(?!.*(composer.json|/.github/|/.idea/|/.git/|/.setting/|/runtime/|/vendor-bin/|/build/))(.*)$#',
    'exclude_files' => [
        '.env', 'LICENSE', 'composer.json', 'composer.lock', 'larmias.phar'
    ],
];