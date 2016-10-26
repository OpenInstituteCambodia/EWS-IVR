var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

var path = {
    'jquery': 'bower_components/jquery/',
    'bootstrap': 'bower_components/bootstrap/',
    'fontAwesome': 'bower_components/font-awesome/',
    'ionicons': 'bower_components/Ionicons/',
    'vue': 'bower_components/vue/',
    'vueResource': 'bower_components/vue-resource/',

};

elixir(function (mix) {
    mix.sass('app.scss')
        .copy(path.jquery + 'dist/jquery.min.js', 'public/js/jquery.js')
        .copy(path.bootstrap + 'dist/js/bootstrap.min.js', 'public/js/bootstrap.js')
        .copy(path.bootstrap + 'dist/css/bootstrap.min.css', 'public/css/bootstrap.css')
        .copy(path.bootstrap + 'dist/fonts', 'public/fonts')
        .copy(path.vue + 'dist/vue.min.js', 'public/js/vue.js')
        .copy(path.vueResource + 'dist/vue-resource.min.js', 'public/js/vue-resource.js')
        .copy(path.fontAwesome + 'fonts', 'public/fonts')
        .copy(path.fontAwesome + 'css/font-awesome.min.css', 'public/css/font-awesome.css')
        .copy(path.ionicons + 'fonts', 'public/fonts')
        .copy(path.ionicons + 'css/ionicons.min.css', 'public/css/ionicons.css');
});

