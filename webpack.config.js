const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

module.exports = {
    ...defaultConfig,
    entry: {
        'block-editor': [ path.resolve( __dirname, 'src/block-editor.js' ) ],
        'block-frontend': [ path.resolve( __dirname, 'src/block-frontend.js' ) ],
    },
    output: {
        path: path.resolve( __dirname, 'build' ), filename: '[name].js',
    },
};