let mix = require('laravel-mix');

const path = require('path');
let directory = path.basename(path.resolve(__dirname));

const source = 'platform/plugins/' + directory;
const dist = 'public/vendor/core/plugins/' + directory;

mix
    .js(source + '/resources/assets/js/marketplace.js', dist + '/js')
    .js(source + '/resources/assets/js/marketplace-product.js', dist + '/js')
    .js(source + '/resources/assets/js/marketplace-vendor.js', dist + '/js')
    .js(source + '/resources/assets/js/marketplace-setting.js', dist + '/js')
    .js(source + '/resources/assets/js/discount.js', dist + '/js')
    .js(source + '/resources/assets/js/store-revenue.js', dist + '/js')
    .vue()

    .sass(source + '/resources/assets/sass/style.scss', dist + '/css')
    .sass(source + '/resources/assets/sass/rtl.scss', dist + '/css')

    .copy(dist + '/js/marketplace.js', source + '/public/js')
    .copy(dist + '/js/marketplace-product.js', source + '/public/js')
    .copy(dist + '/js/marketplace-vendor.js', source + '/public/js')
    .copy(dist + '/js/marketplace-setting.js', source + '/public/js')
    .copy(dist + '/js/discount.js', source + '/public/js')
    .copy(dist + '/js/store-revenue.js', source + '/public/js')
    .copy(dist + '/css/style.css', source + '/public/css')
    .copy(dist + '/css/rtl.css', source + '/public/css');
