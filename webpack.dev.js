const { merge } = require('webpack-merge');
const common = require('./webpack.common.js');
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');

module.exports = {
  // mode: 'development',
  // devtool: 'inline-source-map',
  devServer: {
    contentBase: './resources/js/dist',
    // Allow all headers, as the Plentymarkets Shop is not hosted on the same host, as the dev server
    headers: {
      "Access-Control-Allow-Origin": "*",
      "Access-Control-Allow-Methods": "GET, POST, PUT, DELETE, PATCH, OPTIONS",
      "Access-Control-Allow-Headers": "X-Requested-With, content-type, Authorization"
    }
  },
  optimization: {
    splitChunks: false,
    minimizer: [new UglifyJsPlugin()],
  },
  output: {
    filename: 'findologic_ceres.js',
    chunkFilename: 'findologic_ceres.js',
  },
  plugins: [
    new BundleAnalyzerPlugin()
  ],
  externals: {
    vue: 'Vue'
  },
};
