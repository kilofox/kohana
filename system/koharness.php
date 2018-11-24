<?php

// Configuration for koharness - builds a standalone skeleton Kohana app for running unit tests
return [
    'modules' => [
        'unittest' => __DIR__ . '/vendor/kohana/unittest'
    ],
    'syspath' => __DIR__,
];
