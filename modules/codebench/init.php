<?php

// Static file serving
Route::set('code-bench/media', 'code-bench-media(/<file>)', ['file' => '.+'])
    ->defaults([
        'controller' => 'Codebench',
        'action' => 'media',
        'file' => null,
    ]);

// Catch-all route for Codebench classes to run
Route::set('codebench', 'codebench(/<class>)')
    ->defaults([
        'controller' => 'Codebench',
        'action' => 'index',
        'class' => null
    ]);
