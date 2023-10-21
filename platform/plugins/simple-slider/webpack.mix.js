let mix = require('laravel-mix')

const path = require('path')
let directory = path.basename(path.resolve(__dirname))

const source = 'platform/plugins/' + directory
const dist = 'public/vendor/core/plugins/' + directory

mix
    .js(source + '/resources/assets/js/simple-slider.js', dist + '/js')
    .js(source + '/resources/assets/js/simple-slider-admin.js', dist + '/js')
    .sass(source + '/resources/assets/sass/simple-slider.scss', dist + '/css')

if (mix.inProduction()) {
    mix
        .copy(dist + '/js/simple-slider.js', source + '/public/js')
        .copy(dist + '/js/simple-slider-admin.js', source + '/public/js')
        .copy(dist + '/css/simple-slider.css', source + '/public/css')
}
