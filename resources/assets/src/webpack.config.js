const webpack = require('webpack');
const HtmlWebpackPlugin = require('html-webpack-plugin');

module.exports = {
  entry: './src/main.ts',
  module: {
    rules: [
      { test: /\.ts$/, loader: 'awesome-typescript-loader?silent=true!angular2-template-loader' },
      { test: /\.css/,  loader: 'style-loader!css-loader' },
      { test: /\.scss$/, use: ["style-loader", "sass-loader"] },
      { test: /\.(css|html)/, loader: 'raw-loader' },
      { test: /\.(png|jpe?g|gif|svg|woff|woff2|ttf|eot|ico)$/, loader: 'file-loader?name=fonts/[name].[ext]' },
    ]
  },
  resolve: {
    extensions: ['.ts', '.js']
  },
  plugins: [
    new HtmlWebpackPlugin({
      template: './src/index.html',
      filename: 'index.html',
      inject: 'body'
    }),
    new webpack.DefinePlugin({
      config: JSON.stringify({
        apiUrl: 'http://localhost:4000'
      })
    })
  ],
  optimization: {
    splitChunks: {
      chunks: 'all',
    },
    runtimeChunk: true
  },
};
