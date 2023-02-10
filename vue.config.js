const path = require('path');
const webpack = process.env.NODE_ENV === 'production' ? require('./webpack.prod.js') : require('./webpack.dev.js');

/**
 * @type {import('@vue/cli-service').ProjectOptions}
 */
module.exports = {
  publicPath: 'https://localhost:8080',
  configureWebpack: webpack,
  outputDir: path.resolve(__dirname, 'resources/js/dist'),
  chainWebpack: config => {
    config.plugins.delete('html');
    config.plugins.delete('preload');
    config.plugins.delete('prefetch');
  },
  css: {
    extract: {
      filename: 'findologic_ceres.css',
      chunkFilename: 'findologic_ceres.css',
    },
  },
  devServer: {
    static: {
      directory: './resources/js/dist'
    },
    // Allow all headers, as the Plentymarkets Shop is not hosted on the same host, as the dev server
    headers: {
      'Access-Control-Allow-Origin': '*',
      'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, PATCH, OPTIONS',
      'Access-Control-Allow-Headers': 'X-Requested-With, content-type, Authorization'
    },
    allowedHosts: 'all',
  }
};
