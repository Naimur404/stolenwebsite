let mix = require('laravel-mix');

const path = require('path');
let directory = path.basename(path.resolve(__dirname));

const source = 'platform/plugins/' + directory;
const dist = 'public/vendor/core/plugins/' + directory;

mix
    .js(source + '/resources/assets/js/paypal-payout.js', dist + '/js/paypal-payout.js')

    .copy(dist + '/js/paypal-payout.js', source + '/public/js')
