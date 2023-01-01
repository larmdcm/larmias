<?php

require '../bootstrap.php';

use Larmias\Utils\FileSystem\Finder;

$files1 = Finder::create()
        ->include('../../projects')
        ->exclude(['worker-s','examples/di','projects/larmias-admin/app/System/Services'])
        ->excludeFile('MenuController.php')
        ->includeExt('php')
        ->files();
dump($files1);