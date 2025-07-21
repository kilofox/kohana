<?php

return [
    'default' => [
        'driver' => 'openssl',
        /**
         * The following options must be set:
         *
         * string   key     Secret passphrase.
         * integer  method  The cipher method, one of the return value from openssl_get_cipher_methods().
         */
        'method' => 'AES-256-CTR',
    ],
];
