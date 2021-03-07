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
      _global: ["./base_theme/src/components/_global/_global.js"],
      icons: ["./base_theme/src/components/icons/icons.js"],
      checkbox : ["./base_theme/src/components/checkbox/checkbox.js"],
      select: ["./base_theme/src/components/select/select.js"],
      summernote: ["./base_theme/src/components/summernote/summernote.js"],
      datetimepicker : ["./base_theme/src/components/datetimepicker/datetimepicker.js"],
      daterangepicker : ["./base_theme/src/components/daterangepicker/daterangepicker.js"],
      collapsable_card : ["./base_theme/src/components/collapsable_card/collapsable_card.js"],
      collapsible_widget_card : ["./base_theme/src/components/collapsible_widget_card/collapsible_widget_card.js"],
      file_input : ["./base_theme/src/components/file_input/file_input.js"],
      /**
       * Forms
       */
      search_form : ["./base_theme/src/forms/search_form.js"],
      table_struct_form : ["./base_theme/src/forms/table_struct_form.js"],
      insert_form : ["./base_theme/src/forms/insert_form.js"],
      tree_form: ["./base_theme/src/forms/tree_form.js"],
      /**
       * Views
       */
      side_table_list : ["./base_theme/src/views/side_table_list.js"],
      side_entity_list : ["./base_theme/src/views/side_entity_list.js"],
      table_and_column_selector : ["./base_theme/src/views/table_and_column_selector.js"],
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
