<?php

require __DIR__ . '/bootstrap.php';

use Github\Client;

$client = new Client();
$client->authenticate($_ENV['GITHUB_TOKEN'], \Github\AuthMethod::ACCESS_TOKEN);

var_dump(createRepos($client, 'di'));

function hasRepos(Client $client, string $name): bool
{
    try {
        $response = $client->api('repo')->show('larmdcm', $name);
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function createRepos(Client $client, string $name): bool
{
    if (hasRepos($client, $name)) {
        return true;
    }

    try {
        $response = $client->api('repo')->create($name);
        return true;
    } catch (Throwable $e) {
        return false;
    }
}