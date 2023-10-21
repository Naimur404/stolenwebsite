let mix = require('laravel-mix');

const path = require('path');
let directory = path.basename(path.resolve(__dirname));

const source = 'platform/plugins/' + directory;
const dist = 'public/vendor/core/plugins/' + directory;

mix
    .js(source + '/resources/assets/js/shippo.js', dist + '/js/shippo.js')

    .copy(dist + '/js/shippo.js', source + '/public/js');
