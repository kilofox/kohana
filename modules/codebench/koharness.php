<?php

// Configuration for koharness - builds a standalone skeleton Kohana app for running unit tests
return [
    'modules' => [
        'codebench' => __DIR__,
        'unittest' => __DIR__ . '/vendor/kohana/unittest'
    ],
];
