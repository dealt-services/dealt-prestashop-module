const path = require("path");
const webpack = require("webpack");
const keepLicense = require("uglify-save-license");

const psRootDir = process.env._PS_ROOT_DIR_;
const psJsDir = path.resolve(psRootDir, "admin-dev/themes/new-theme/js");
const psAppDir = path.resolve(psJsDir, "app");
const psComponentsDir = path.resolve(psJsDir, "components");

const config = {
  entry: {
    "dealt.admin.config.form": ["./js/admin/form/dealt.configuration.form.js"],
    "dealt.admin.offer.form": ["./js/admin/form/dealt.offer.form.js"],
    "dealt.admin.offer.list": ["./js/admin/grid/dealt.offer.list.js"],
    "dealt.admin.mission.list": ["./js/admin/grid/dealt.mission.list.js"],
    "dealt.front.offer.product": ["./js/front/dealt.offer.product.js"],
    "dealt.front.offer.cart": ["./js/front/dealt.offer.cart.js"],
  },
  output: {
    path: path.resolve(__dirname, "public"),
    filename: "[name].bundle.js",
  },
  //devtool: 'source-map', // uncomment me to build source maps (really slow)
  resolve: {
    extensions: [".js"],
    alias: {
      "@app": psAppDir,
      "@components": psComponentsDir,
    },
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        include: path.resolve(__dirname, "js"),
        use: [
          {
            loader: "babel-loader",
            options: {
              presets: [["es2015", { modules: false }]],
            },
          },
        ],
      },
      {
        test: /\.js$/,
        include: path.resolve(
          __dirname,
          "../../../admin-dev/themes/new-theme/js"
        ),
        use: [
          {
            loader: "babel-loader",
            options: {
              presets: [["es2015", { modules: false }]],
            },
          },
        ],
      },
    ],
  },
  plugins: [],
};

if (process.env.NODE_ENV === "production") {
  config.plugins.push(
    new webpack.optimize.UglifyJsPlugin({
      sourceMap: false,
      compress: {
        sequences: true,
        conditionals: true,
        booleans: true,
        if_return: true,
        join_vars: true,
        drop_console: false,
      },
      output: {
        comments: keepLicense,
      },
    })
  );
} else {
  config.plugins.push(new webpack.HotModuleReplacementPlugin());
}

module.exports = config;
