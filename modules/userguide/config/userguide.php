<?php

return [
    // Enable the API browser.  true or false
    'api_browser' => true,
    // Enable these packages in the API browser.  true for all packages, or a string of comma seperated packages, using 'None' for a class with no @package
    // Example: 'api_packages' => 'Kohana,Kohana/Database,Kohana/ORM,None',
    'api_packages' => true,
    // Enables Disqus comments on the API and User Guide pages
    'show_comments' => Kohana::$environment === Kohana::PRODUCTION,
    // Leave this alone
    'modules' => [
        // This should be the path to this module's userguide pages, without the 'guide/'. Ex: '/guide/modulename/' would be 'modulename'
        'userguide' => [
            // Whether this module's userguide pages should be shown
            'enabled' => true,
            // The name that should show up on the userguide index page
            'name' => 'Userguide',
            // A short description of this module, shown on the index page
            'description' => 'Documentation viewer and api generation.',
            // Copyright message, shown in the footer for this module
            'copyright' => '&copy; 2008–2014 Kohana Team',
        ]
    ],
    // Set transparent class name segments
    'transparent_prefixes' => [
        'Kohana' => true,
    ]
];
