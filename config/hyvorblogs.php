<?php

return [

    /**
     * Sett up one or more blogs within your application
     */
    'blogs' => [
        [
            /**
             * The subdomain given by Hyvor Blogs
             * Sign up at https://blogs.hyvor.com/console to create a new one
             */
            'subdomain' => 'hb-blog',

            /**
             * Generate an API key for your blog and paste it here
             */
            'delivery_api_key' => '',


            'webhook_secret' => null,

            /**
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

    'hb_base_url' => 'http://blogs.hyvor.test:8082',

];
