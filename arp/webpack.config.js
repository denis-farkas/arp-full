const Encore = require("@symfony/webpack-encore");
const webpack = require("webpack"); // Ajouter cette ligne pour utiliser webpack plugins

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || "dev");
}

Encore
  // directory where compiled assets will be stored
  .setOutputPath("public/build/")
  // public path used by the web server to access the output path
  .setPublicPath("/build")
  // only needed for CDN's or subdirectory deploy
  //.setManifestKeyPrefix('build/')

  /*
   * ENTRY CONFIG
   *
   * Each entry will result in one JavaScript file (e.g. app.js)
   * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
   */
  .addEntry("app", "./assets/app.js")
  .addEntry("calendar", "./assets/calendar.js")

  // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
  .splitEntryChunks()

  // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
  .enableStimulusBridge("./assets/controllers.json")

  // will require an extra script tag for runtime.js
  // but, you probably want this, unless you're building a single-page app
  .enableSingleRuntimeChunk()

  /*
   * FEATURE CONFIG
   *
   * Enable & configure other features below. For a full
   * list of features, see:
   * https://symfony.com/doc/current/frontend.html#adding-more-features
   */

  .enableBuildNotifications()
  .enableSourceMaps(!Encore.isProduction())
  // enables hashed filenames (e.g. app.abc123.css)
  .enableVersioning(Encore.isProduction())

  // configure Babel pour utiliser les plugins nécessaires
  .configureBabel((config) => {
    // Ajouter le plugin pour résoudre les problèmes avec React
    config.plugins.push("@babel/plugin-transform-runtime");
  })

  // enables and configure @babel/preset-env polyfills
  .configureBabelPresetEnv((config) => {
    config.useBuiltIns = "usage";
    config.corejs = 3; // Format corrigé
  })

  // Activer le post CSS
  .enablePostCssLoader()

  // Activer React une seule fois
  .enableReactPreset()

  // Règle spécifique pour corriger l'erreur nmd
  .addRule({
    test: /\.jsx?$/,
    include:
      /node_modules[\\/](@symfony[\\/]stimulus-bridge|@symfony[\\/]ux-react)/,
    use: {
      loader: "babel-loader",
      options: {
        presets: ["@babel/preset-env", "@babel/preset-react"],
      },
    },
  })

  // Ajouter le plugin pour corriger l'erreur nmd
  .addPlugin(
    new webpack.ProvidePlugin({
      React: "react",
      "__webpack_require__.nmd": function () {
        return function (module) {
          return module;
        };
      },
    })
  )

  // Copier les images
  .copyFiles({
    from: "./assets/images",
    to: "images/[path][name].[ext]",
  });

// Exporter la configuration
module.exports = Encore.getWebpackConfig();
