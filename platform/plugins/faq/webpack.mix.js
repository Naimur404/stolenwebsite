let mix = require('laravel-mix')

const path = require('path')
let directory = path.basename(path.resolve(__dirname))

const source = 'platform/plugins/' + directory
const dist = 'public/vendor/core/plugins/' + directory

mix
    .sass(source + '/resources/assets/sass/faq.scss', dist + '/css')
    .js(source + '/resources/assets/js/faq.js', dist + '/js')

if (mix.inProduction()) {
    mix
        .copy(dist + '/css/faq.css', source + '/public/css')
        .copy(dist + '/js/faq.js', source + '/public/js')
}
