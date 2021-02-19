const path = require('path');
const prod = require('./webpack.dev.js');

/**
 * @type {import('@vue/cli-service').ProjectOptions}
 */
module.exports = {
  configureWebpack: prod,
  outputDir: path.resolve(__dirname, 'resources/js/dist'),
  chainWebpack: config => {
    config.plugins.delete('html')
    config.plugins.delete('preload')
    config.plugins.delete('prefetch')
  },
  css: {
    extract: {
      filename: 'findologic_ceres.css',
      chunkFilename: 'findologic_ceres.css',
    },
  },
}
