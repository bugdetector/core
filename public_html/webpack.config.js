const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const path = require("path");
const autoprefixer = require("autoprefixer");
const globImporter = require('node-sass-glob-importer');

const dirNode = 'node_modules';
const dirApp = __dirname;

module.exports = (env, argv) => {
  const isDevMode = argv.mode === "development";
  return {
    devtool: isDevMode ? 'source-map' : false,
    entry: {
      /**
       * Components
       */
      _global: ["./src/components/_global/_global.js"],
      icons: ["./src/components/icons/icons.js"],
      checkbox : ["./src/components/checkbox/checkbox.js"],
      select: ["./src/components/select/select.js"],
      summernote: ["./src/components/summernote/summernote.js"],
      datetimepicker : ["./src/components/datetimepicker/datetimepicker.js"],
      daterangepicker : ["./src/components/daterangepicker/daterangepicker.js"],
      collapsable_card : ["./src/components/collapsable_card/collapsable_card.js"],
      /**
       * Forms
       */
      search_form : ["./src/forms/search_form.js"],
      table_struct_form : ["./src/forms/table_struct_form.js"],
      insert_form : ["./src/forms/insert_form.js"],
      /**
       * Screens
       */
      table_screen : ["./src/screens/table_screen.js"],
      translation_screen : ["./src/screens/translation_screen.js"],
      /**
       * Views
       */
      side_table_list : ["./src/views/side_table_list.js"],
      side_entity_list : ["./src/views/side_entity_list.js"],
    },
    resolve: {
      modules: [
        dirNode,
        dirApp,
      ],
    },
    resolve: {
      extensions: ['.js', '.scss'],
      alias: {
        '@': path.resolve(__dirname, 'src')
      }
    },
    module: {
      rules: [
        {
          test: /\.(sa|sc|c)ss$/,
          use: [
            MiniCssExtractPlugin.loader,
            {
              loader: 'css-loader', options: {
                sourceMap: isDevMode,
              }
            },
            { loader: 'postcss-loader', options: { 
              sourceMap: isDevMode,
              plugins:()=>[
                autoprefixer()
              ]
             } },
            {
              loader: 'sass-loader',
              options: {
                sourceMap: isDevMode,
                webpackImporter: false,
                sassOptions: {
                  importer: globImporter(),
                  includePaths: [
                    path.resolve(__dirname, dirNode), 
                  ]
                }
              }
            },
          ],
        },
        {
          test: /\.(jpe?g|ttf|woff|woff2|eot|png|gif)$/,
          loader: 'file-loader',
          options: {
            name: '[path][name].[ext]',
          },
        },
        {
          test: /\.js$/,
          exclude: /(node_modules|bower_components)/,
          use: [
            {
            loader: "babel-loader",
            options: {
                presets: [["@babel/preset-env", { modules: false }]],
                compact: true
              }
            },
            "webpack-import-glob-loader",
          ]
        },
        {
          test: /\.svg$/,
          loader: 'svg-inline-loader'
        }

      ],
     
    },
    output: {
      path: path.resolve(__dirname, "./dist"),
      filename: "[name]/[name].js",
      publicPath: "../../",
     
    },
    plugins: [
      new MiniCssExtractPlugin({
        filename: '[name]/[name].css'
      })
    ]
  };
};
