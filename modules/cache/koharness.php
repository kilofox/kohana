<?php

// Configuration for koharness - builds a standalone skeleton Kohana app for running unit tests
return [
    'kohana_version' => '3.3',
    'modules' => [
        'cache' => __DIR__,
        'unittest' => __DIR__ . '/vendor/kohana/unittest'
    ],
];
