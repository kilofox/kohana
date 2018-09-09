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
    'mcrypt' => [
        'driver' => 'mcrypt',
        /**
         * The following options must be set:
         *
         * string   key     Secret passphrase.
         * integer  cipher  Encryption cipher, one of the Mcrpyt cipher constants.
         * integer  mode    Encryption mode, one of MCRYPT_MODE_*.
         */
        'cipher' => MCRYPT_RIJNDAEL_128,
        'mode' => MCRYPT_MODE_NOFB,
    ]
];
