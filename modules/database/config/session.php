<?php

return [
    'database' => [
        /**
         * Database settings for session storage.
         *
         * string   group  configuration group name
         * string   table  session table name
         * integer  gc     number of requests before gc is invoked
         * columns  array  custom column names
         */
        'group' => 'default',
        'table' => 'sessions',
        'gc' => 500,
        'columns' => [
            /**
             * session_id:  session identifier
             * last_active: timestamp of the last activity
             * contents:    serialized session data
             */
            'session_id' => 'session_id',
            'last_active' => 'last_active',
            'contents' => 'contents'
        ],
    ],
];
