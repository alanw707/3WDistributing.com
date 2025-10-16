/**
 * PostCSS configuration to ensure prefixing and production minification.
 * `ctx.env` is controlled by @wordpress/scripts (development vs. production).
 */
module.exports = (ctx) => ({
  plugins: [
    require('autoprefixer'),
    ctx.env === 'production'
      ? require('cssnano')({
          preset: ['default', { discardComments: { removeAll: true } }],
        })
      : false,
  ].filter(Boolean),
});
