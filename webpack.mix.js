const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .combine([
        'node_modules/jquery/dist/jquery.slim.js',
        'node_modules/bootstrap/dist/js/bootstrap.bundle.js',
        // 'node_modules/@fortawesome/fontawesome-free/js/all.js',
        'node_modules/chart.js/dist/Chart.js',
        'node_modules/moment/moment.js',
        'node_modules/tempusdominus-core/build/js/tempusdominus-core.js',
        'node_modules/tempusdominus-bootstrap-4/build/js/tempusdominus-bootstrap-4.js',
    ], 'public/js/vendor.js')
    .combine([
        'node_modules/@fortawesome/fontawesome-free/css/all.css',
        'node_modules/tempusdominus-bootstrap-4/build/css/tempusdominus-bootstrap-4.css'
    ], 'public/css/vendor.css')
    .copy('node_modules/bootstrap/dist/css/bootstrap.min.css', 'public/css/')
    .copy('node_modules/bootstrap/dist/css/bootstrap.min.css.map', 'public/css/')
    .copy('node_modules/@fortawesome/fontawesome-free/webfonts/', 'public/webfonts/');
