<?php

declare(strict_types=1);

use function Larmias\Support\env;

return [
    'default' => env('ENCRYPT_DEFAULT', 'aes'),
    'handlers' => [
        'aes' => [
            'driver' => \Larmias\Encryption\Driver\OpenSSL::class,
            'packer' => \Larmias\Codec\Packer\SecureBase64Packer::class,
            'key' => 'aBigsecret_ofAtleast32Characters',
            'iv' => null,
            'cipher' => 'aes-128-cbc',
            'options' => \OPENSSL_RAW_DATA,
            'digest' => 'SHA512',
        ]
    ]
];