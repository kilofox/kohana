Changes that should happen when you deploy. (Production)

## Setting up a production environment

There are a few things you'll want to do with your application before moving into production.

1. See the [Bootstrap page](bootstrap) in the docs.
   This covers most of the global settings that would change between environments.
   As a general rule, you should enable caching and disable profiling ([Kohana::init] settings) for production sites.
   [Route::cache] can also help if you have a lot of routes.
2. Turn on APC or some kind of opcode caching.
   This is the single easiest performance boost you can make to PHP itself. The more complex your application, the bigger the benefit of using opcode caching.

        /**
         * Set the environment string by the domain (defaults to Kohana::DEVELOPMENT).
         */
        Kohana::$environment = $_SERVER['SERVER_NAME'] !== 'localhost' ? Kohana::PRODUCTION : Kohana::DEVELOPMENT;
        /**
         * Initialise Kohana based on environment
         */
        Kohana::init([
            'base_url' => '/',
            'index_file' => false,
            'profile' => Kohana::$environment !== Kohana::PRODUCTION,
            'caching' => Kohana::$environment === Kohana::PRODUCTION,
        ]);
