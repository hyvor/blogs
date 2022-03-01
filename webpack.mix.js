const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */


// console
mix.js('resources/js/console/console.js', 'public/js').react();
mix.sass('resources/css/console/console.scss', 'public/css');


// landing
mix.sass('resources/css/landing/landing.scss', 'public/css');