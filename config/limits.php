<?php

/**
 * All hard limits go here
 */
return [

    /**
     * Who needs more than 1000?
     */
    'max_redirects_per_blog' => 1000,

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
     * Template-related limitations
     */
    'max_template_files_per_blog' => 50,
    'max_template_file_size' => 50 * 1000, // 50kb
    'max_assets_per_blog' => 30,
    'max_asset_file_size' => 1000 * 1000, // 1MB
    'max_style_files_per_blog' => 50,
    'max_lang_files_per_blog' => 100, // same as max languages



];