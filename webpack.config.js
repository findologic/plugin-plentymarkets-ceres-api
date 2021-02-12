const path = require('path');

module.exports = {
    entry: './resources/js/src/index.js',
    output: {
        filename: 'findologic_ceres.js',
        path: path.resolve(__dirname, 'resources/js/dist'),
    },
};
