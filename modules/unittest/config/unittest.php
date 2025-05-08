<?php

return [
    // If you don't use a whitelist then only files included during the request will be counted
    // If you do, then only whitelisted items will be counted
    'use_whitelist' => true,
    // Items to whitelist, only used in cli
    'whitelist' => [
        // Should the app be whitelisted?
        // Useful if you just want to test your application
        'app' => true,
        // Set to [true] to include all modules, or use an array of module names
        // (the keys of the array passed to Kohana::modules() in the bootstrap)
        // Or set to "false" to exclude all modules
        'modules' => [true],
        // If you don't want the Kohana code coverage reports to pollute your app's,
        // then set this to false
        'system' => true,
    ],
    // Does what it says on the tin
    // Blacklisted files won't be included in code coverage reports
    // If you use a whitelist then the blacklist will be ignored
    'use_blacklist' => false,
    // List of individual files/folders to blacklist
    'blacklist' => [
    ],
];
