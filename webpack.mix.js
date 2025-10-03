let mix = require('laravel-mix');

mix.js("src/frontend.js", "assets/js/frontend.js").webpackConfig({
    externals: {
        jquery: 'jQuery', // Usa o jQuery global (do WordPress)
    }
});

mix.js("src/admin.js", "assets/js/admin.js").webpackConfig({
    externals: {
        jquery: 'jQuery', // Usa o jQuery global (do WordPress)
    }
});

mix.postCss("src/frontend.css", "assets/css/frontend.css").options({
    processCssUrls: false
});