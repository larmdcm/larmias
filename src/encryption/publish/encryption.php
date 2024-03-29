<?php

declare(strict_types=1);

return [
    'default' => 'aes',
    'handlers' => [
        'aes' => [
            'driver' => \Larmias\Encryption\Driver\OpenSSL::class,
            'packer' => \Larmias\Codec\Packer\Base64Packer::class,
            'key' => 'aBigsecret_ofAtleast32Characters',
            'iv' => null,
            'cipher' => 'aes-128-cbc',
            'options' => \OPENSSL_RAW_DATA,
            'digest' => 'SHA512',
        ]
    ]
];