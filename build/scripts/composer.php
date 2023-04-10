<?php

require __DIR__ . '/bootstrap.php';

use Symfony\Component\Finder\Finder;

$composerFile = PROJECT_PATH . '/composer.json';
$composer = json_decode(file_get_contents($composerFile), true);

$files = Finder::create()->files()->in([PROJECT_PATH . '/src'])->name(['composer.json']);
$subComposer = [];


/** @var SplFileInfo $file */
foreach ($files as $file) {
    $info = json_decode($file->getContents(), true);
    $subComposer[] = ['name' => $info['name'], 'base_path' => ''];
}

foreach ($subComposer as $item) {
    $composer['replace'][$item['name']] = 'self.version';
}

file_put_contents(__DIR__ . '/composer.json', json_encode($composer, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));