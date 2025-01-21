const path = require("path");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const { WebpackManifestPlugin } = require("webpack-manifest-plugin");

const pagesAssets = () => {
  const terraConfig = require("./terra.json");
  const entries = [];

  terraConfig.pages.forEach((page) => {
    entries[`${page.id}_scripts`] = `./${page.dir}/scripts/${page.id}.ts`;
  });

  return entries;
};

module.exports = {
  entry: {
    theme_scripts: "./assets/scripts/theme.ts",
    theme_styles: "./assets/styles/theme.css",
    ...pagesAssets(),
  },
  output: {
    path: path.resolve(__dirname, "dist"),
    filename: "js/[name].[contenthash].bundle.js",
    publicPath: "auto",
    clean: true,
  },
  module: {
    rules: [
      {
        test: /\.(js|ts)$/,
        exclude: /node_modules/,
        use: "ts-loader",
      },
      {
        test: /\.css$/,
        use: [MiniCssExtractPlugin.loader, "css-loader", "postcss-loader"],
      },
    ],
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: "css/[name].[contenthash].bundle.css",
    }),
    new WebpackManifestPlugin({
      seed: {},
      generate: (seed, files) => {
        const manifest = {};

        files.forEach((file) => {
          if (file.name === "theme_styles.js") return;

          const entryName = file.name.split("/").pop().split(".")[0];
          manifest[entryName] = file.path.replace("auto/", "");
        });

        return manifest;
      },
    }),
  ],
};
