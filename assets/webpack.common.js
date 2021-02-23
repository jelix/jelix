const path = require('path');

module.exports = {
    entry: {
        jforms_jquery: './src/jforms_jquery/jforms_jquery.js'
    },
    output: {
        filename: '[name].js',
        chunkFilename: '[name].bundle.js',
        path: path.resolve(__dirname, '../lib/jelix-www/js/'),
    },
    externals: {
        jquery: '$'
    }
};
