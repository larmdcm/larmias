<?php

require __DIR__ . '/bootstrap.php';

use Symfony\Component\Finder\Finder;

$composerFile = PROJECT_PATH . '/composer.json';
$composer = json_decode(file_get_contents($composerFile), true);

$files = Finder::create()->files()->in([PROJECT_PATH . '/src'])->name(['composer.json']);
$subComposer = [];

function handleSubComposer(array $composer): array
{
    $jsonData = $composer['json_data'];
    /** @var SplFileInfo $file */
    $providerPath = $composer['base_path'] . '/src/Providers';
    $namespace = getPsr4Namespace($jsonData);
    if (!str_ends_with($namespace, '\\')) {
        $jsonData['autoload']['psr-4'][$namespace] = $namespace . '\\';
        $namespace .= '\\';
    }
    if (is_dir($providerPath)) {
        $providers = [];
        foreach (Finder::create()->files()->in([$providerPath])->name(['*.php']) as $file) {
            $provider = $namespace . 'Providers\\' . pathinfo($file->getFilename(), PATHINFO_FILENAME);
            $providers[] = $provider;
        }

        if (!empty($providers)) {
            $jsonData['extra']['larmias']['providers'] = $providers;
        }
    }


    putComposerFile($composer['file_path'], $jsonData);

    $composer['json_data'] = $jsonData;

    return $composer;
}

function getPsr4Namespace(array $jsonData): string
{
    $psr4 = $jsonData['autoload']['psr-4'] ?? [];
    return strval(key($psr4));
}

function putComposerFile(string $file, array $composer): void
{
    file_put_contents($file, json_encode($composer, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
}

/** @var SplFileInfo $file */
foreach ($files as $file) {
    $jsonData = json_decode($file->getContents(), true);
    $info = ['name' => $jsonData['name'], 'base_path' => dirname($file->getRealPath()), 'file_path' => $file->getRealPath(), 'json_data' => $jsonData];
    $info = handleSubComposer($info);
    $subComposer[] = $info;
}


$composer['extra']['larmias']['providers'] = [];

foreach ($subComposer as $item) {
    $composer['replace'][$item['name']] = 'self.version';
    $providers = (array)($item['json_data']['extra']['larmias']['providers'] ?? []);
    $composer['extra']['larmias']['providers'] = [...$composer['extra']['larmias']['providers'], ...$providers];
}

putComposerFile($composerFile, $composer);