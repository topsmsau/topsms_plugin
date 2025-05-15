const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const DependencyExtractionWebpackPlugin = require('@wordpress/dependency-extraction-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const path = require('path');
const exec = require('child_process').exec;

const requestToExternal = (request) => {
  const wcDepMap = {
    '@woocommerce/components': ['window', 'wc', 'components'],
    '@woocommerce/csv-export': ['window', 'wc', 'csvExport'],
    '@woocommerce/currency': ['window', 'wc', 'currency'],
    '@woocommerce/date': ['window', 'wc', 'date'],
    '@woocommerce/navigation': ['window', 'wc', 'navigation'],
    '@woocommerce/number': ['window', 'wc', 'number'],
    '@woocommerce/settings': ['window', 'wc', 'wcSettings'],
  };

  if (wcDepMap[request]) {
    return wcDepMap[request];
  }
};

const requestToHandle = (request) => {
  const wcHandleMap = {
    '@woocommerce/components': 'wc-components',
    '@woocommerce/csv-export': 'wc-csv',
    '@woocommerce/currency': 'wc-currency',
    '@woocommerce/date': 'wc-date',
    '@woocommerce/navigation': 'wc-navigation',
    '@woocommerce/number': 'wc-number',
    '@woocommerce/settings': 'wc-settings',
  };

  if (wcHandleMap[request]) {
    return wcHandleMap[request];
  }
};

// Remove the default CSS/SCSS rules from WordPress config
const filteredRules = defaultConfig.module.rules.filter(
  (rule) =>
    !(
      rule.test &&
      (rule.test.toString().includes('.css') ||
        rule.test.toString().includes('.scss'))
    )
);

module.exports = {
  ...defaultConfig,
  entry: {
    index: path.resolve(process.cwd(), 'src/index.js'),
    // Add other entry points if needed
  },
  output: {
    ...defaultConfig.output,
    filename: '[name].js',
    path: path.resolve(process.cwd(), 'build'),
  },
  plugins: [
    ...defaultConfig.plugins.filter(
      (plugin) =>
        plugin.constructor.name !== 'DependencyExtractionWebpackPlugin' &&
        plugin.constructor.name !== 'MiniCssExtractPlugin'
    ),
    new DependencyExtractionWebpackPlugin({
      injectPolyfill: true,
      requestToExternal,
      requestToHandle,
    }),
    new MiniCssExtractPlugin({
      filename: '[name].css',
    }),
    {
      apply: (compiler) => {
        compiler.hooks.afterEmit.tap('AfterEmitPlugin', (compilation) => {
          exec(
            'wp i18n make-pot src languages/wc-admin-topsms-analytics.pot --domain=wc-admin-topsms-analytics',
            (err, stdout, stderr) => {
              if (stdout) process.stdout.write(stdout);
              if (stderr) process.stderr.write(stderr);
            }
          );
        });
      },
    },
  ],
  module: {
    ...defaultConfig.module,
    rules: [
      ...filteredRules,
      {
        test: /\.s?css$/,
        use: [
          MiniCssExtractPlugin.loader,
          {
            loader: 'css-loader',
            options: {
              sourceMap: true,
            },
          },
          {
            loader: 'sass-loader',
            options: {
              sourceMap: true,
              sassOptions: {
                outputStyle: 'compressed',
              },
            },
          },
        ],
      },
    ],
  },
};
