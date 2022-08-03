<?php

/**
 * All hard limits go here
 */
return [

    /**
     * Number of days for the trial
     */
    'trial_days' => 14,

    /**
     * This is the Pages limit (static) not posts
     * Even Webflow has a limit of 100
     * https://university.webflow.com/lesson/pages-panel#how-many-pages-can-i-create
     *
     * Having a large number of static pages doesn't really undermine Hyvor Blog performances
     * But pages are not the focus of HB.
     * Therefore, we put this limit
     * If anyone needs more than this in the future (which is unlikely),
     * we will need to update it or remove it
     *
     * Another reason to have this limit is that currently console Pages section doesn't use limit-offset
     */
    'max_pages_per_blog' => 100,

    /**
     * Who needs more than 1000?
     */
    'max_redirects_per_blog' => 1000,

    /**
     * 10 per header
     * 10 per footer
     */
    'max_navigations_per_type_per_blog' => 10,

    /**
     * This is a guess.
     * I don't think a blog will have more than 100 separate languages
     * If someone exceeds this, we'll need to change it
     */
    'max_languages_per_blog' => 100,

    /**
     * All routes are fetched when rendering content
     * therefore, we have to make sure there are no large number of routes
     * Users will rarely need 50 routes. If someone creates custom pages, a little more will be needed
     */
    'max_routes_per_blog' => 50,

    /**
     * Webhooks means a HTTP calls on event
     * They are not "soft"
     * So, 5 per blog is a good limit
     * Users can different events for each webhook
     */
    'max_webhooks_per_blog' => 5,

    /**
     * Media max uploading size for any file type
     */
    'max_media_upload_size_kb' => 50 * 1000, // 50MB

    /**
     * Who needs more than 50?
     */
    'max_api_keys_per_blog' => 50,

    /**
     * Sitemap
     * Used in posts
     */
    'max_entries_per_sitemap' => 2500,

    /**
     * Template-related limitations
     */
    'max_theme_zip_size_kb' => 50 * 1000, // 50MB
    'max_asset_file_size' => 2 * 1000 * 1000, // 2MB

    /**
     * String lengths
     */
    'max_blog_name_length' => 160,
    'max_blog_description_length' => 255,
    'max_post_description_length' => 350,
    'max_post_title_length' => 255,

];
