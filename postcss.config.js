// postcss.config.js
module.exports = {
  plugins: [
    // Other plugins...
    require('postcss-prefix-selector')({
      prefix: '.topsms-app',  
    }),
    require('tailwindcss'),
    require('autoprefixer'),
    // Other plugins...
  ]
}