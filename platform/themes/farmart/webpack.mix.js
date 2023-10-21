let mix = require('laravel-mix');

const purgeCss = require('@fullhuman/postcss-purgecss');

const path = require('path');
let directory = path.basename(path.resolve(__dirname));

const source = 'platform/themes/' + directory;
const dist = 'public/themes/' + directory;

mix
    .sass(source + '/assets/sass/marketplace.scss', dist + '/css')
    .sass(source + '/assets/sass/marketplace-rtl.scss', dist + '/css')

    .sass(
        source + '/assets/sass/style.scss',
        dist + '/css',
        {},
        [
            purgeCss({
                content: [
                    source + '/assets/js/components/*.vue',
                    source + '/assets/js/components/**/*.vue',
                    source + '/layouts/*.blade.php',
                    source + '/partials/*.blade.php',
                    source + '/partials/**/*.blade.php',
                    source + '/partials/**/**/*.blade.php',
                    source + '/views/*.blade.php',
                    source + '/views/**/*.blade.php',
                    source + '/views/**/**/*.blade.php',
                    source + '/views/**/**/**/*.blade.php',
                    source + '/widgets/**/templates/frontend.blade.php',
                ],
                defaultExtractor: content => content.match(/[\w-/.:]+(?<!:)/g) || [],
                safelist: [
                    /^owl-/,
                    /^button-loading/,
                    /^slick-/,
                    /^noUi-/,
                    /^pagination/,
                    /^page-/,
                    /^btn--/,
                    /^fa-/,
                    /^fade-/,
                    /show-admin-bar/,
                    /active/,
                    /show/,
                    /timer/,
                    /digits/,
                    /text/,
                    /divider/,
                    /days/,
                    /hours/,
                    /minutes/,
                    /seconds/,
                    /expire-countdown/,
                    /countdown-wrapper/,
                    /header--sticky/
                ],
            })
        ])
    .sass(source + '/assets/sass/style-rtl.scss', dist + '/css')

    .js(source + '/assets/js/marketplace.js', dist + '/js')
    .js(source + '/assets/js/main.js', dist + '/js')
    .js(source + '/assets/js/icons-field.js', dist + '/js')

    .copy(dist + '/css/style.css', source + '/public/css')
    .copy(dist + '/css/style-rtl.css', source + '/public/css')
    .copy(dist + '/css/marketplace.css', source + '/public/css')
    .copy(dist + '/css/marketplace-rtl.css', source + '/public/css')
    .copy(dist + '/js/marketplace.js', source + '/public/js')
    .copy(dist + '/js/main.js', source + '/public/js')
    .copy(dist + '/js/icons-field.js', source + '/public/js');
