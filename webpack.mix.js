let mix = require('laravel-mix')
const path = require('path')

mix.alias({
  vue$: path.join(__dirname, 'node_modules/vue/dist/vue.esm-bundler.js'),
  '@': __dirname + '/resources/js',
})

mix
  .js('resources/js/app.js', 'public/js')
  .vue()
  .extract()
  .postCss('resources/css/app.css', 'public/css', [
    require('postcss-import'),
    require('tailwindcss/nesting')(require('postcss-nesting')),
    require('tailwindcss'),
    require('autoprefixer'),
  ])
  .version()
  .disableNotifications()
  /**
   * @see https://github.com/JeffreyWay/laravel-mix/issues/287
   */
  .options({
    processCssUrls: mix.inProduction(),
  })
  .webpackConfig({
    stats: {
      children: true,
    },
    // Not used ATM
    output: {
      filename: '[name].js',
      chunkFilename: 'js/chunks/[name].js',
    },
  })

mix.copy('node_modules/mitt/dist/mitt.mjs.map', 'public/js/mitt.mjs.map')
