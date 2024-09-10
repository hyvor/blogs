<?php

return [

    /**
     * This is the domain that the app is running on.
     * Routes are only accessible from this domain.
     * @todo: refactor this into `route.` setting
     */
    'domain' => env('APP_DOMAIN', '{any}'),

    /**
     * Which component is this?
     * See `src/InternalApi/ComponentType.php` for available components
     *
     * core - hyvor.com
     * talk - talk.hyvor.com
     * ..
     */
    'component' => 'blogs',

    'auth' => [

        /**
         * Whether to add auth routes
         */
        'routes' => true,

        /**
         * Login provider to use
         * 
         * - hyvor: Hyvor API (default, requires hyvor.com self-hosted)
         * - fake: Fake login (for testing)
         */
        'provider' => env('AUTH_PROVIDER', 'fake'),

        /**
         * Hyvor Login settings
         */
        'hyvor' => [
            /**
             * @deprecated
             * HYVOR Public URL
             * Users are redirected to this URL to login/signup
             */
            'url' => env('AUTH_HYVOR_URL', 'https://hyvor.com'),

            /**
             * HYVOR Private URL (for internal API calls)
             * This is only required if you have HYVOR running on a private network
             * ex: http://0.0.0.1
             */
            'private_url' => env('AUTH_HYVOR_PRIVATE_URL'),
        ],

        'fake' => [
            /**
             * Fake user ID
             */
            'user_id' => env('AUTH_FAKE_USER_ID', 1),
        ]

    ],

    'i18n' => [

        /**
         * Folder that contains the locale JSON files
         */
        'folder' => base_path('locales'),

        /**
         * Default locale
         */
        'default' => 'en-US',

    ],

    'media' => [

        'path' => 'api/media',

        'disk' => 'public'

    ]

];