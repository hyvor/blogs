<?php

return [

    /**
     * Tutorial: <https://blogs.hyvor.com/blog/laravel>
     *
     * Set up one or more blogs within your application
     */
    'blogs' => [
        [
            /**
             * @required
             *
             * The subdomain given by Hyvor Blogs
             * Sign up at https://blogs.hyvor.com/console to create a new one
             */
            'subdomain' => 'blog',

            /**
             * @required
             *
             * API Key to access the Delivery API of your blog
             * Console -> Settings -> API Keys -> Create an API key for Delivery API
             * Paste the key here
             */
            'delivery_api_key' => 'd9a17c17290c4f1c87d67fe0ac88706f',

            /**
             * Optional but recommended
             * As per the tutorial, you should set up a webhook with all cache events at
             * Console -> Settings -> Webhooks
             * Then, copy the secret here for webhook validation
             * If the secret is null, no validation is done
             */
            'webhook_secret' => '936a14f08d8be7f6f94c08a4fdc590ef',

            /**
             * @required
             *
             * Where should we host the blog?
             * If the value is /blog, all /blog/* routes will be reserved for the blog
             */
            'route' => '/blog',

            /**
             * What Laravel cache store should we use for caching?
             * null = use default
             * Or, you can set it to any cache store name defined in your config/cache.php file.
             */
            'cache_store' => null,

            /**
             * Need any middleware? Add them here
             */
            'middleware' => [],
        ],
        /**
         * You can add more blogs here ;)
         */
    ],

    /**
     * Hyvor Blog's Base URL to call the delivery API
     * Only useful for package maintainers
     */
    'hb_base_url' => 'https://blogs.hyvor.com',

];
