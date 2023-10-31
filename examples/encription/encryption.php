<?php

return [
    'default' => 'aes',
    'handlers' => [
        'aes' => [
            'driver' => \Larmias\Encryption\Drivers\OpenSSL::class,
            'data_coding' => \Larmias\Support\Encryption\Base64::class,
            'key' => 'aBigsecret_ofAtleast32Characters',
            'iv' => null,
            'cipher' => 'aes-128-cbc',
            'options' => \OPENSSL_RAW_DATA,
            'digest' => 'SHA512',
        ]
    ]
];