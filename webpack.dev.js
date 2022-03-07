const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
const TerserPlugin = require('terser-webpack-plugin');

module.exports = {
  optimization: {
    splitChunks: false,
    minimizer: [new TerserPlugin()],
  },
  output: {
    filename: 'findologic_ceres.js',
    chunkFilename: 'findologic_ceres.js'
  },
  plugins: [
    // Uncomment if you want to check the bundle size:
    // new BundleAnalyzerPlugin()
  ],
  externals: {
    vue: 'Vue',
    vuex: 'Vuex'
  },
};
