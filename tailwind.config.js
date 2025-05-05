/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
      './admin/src/blocks/**/*.{js,jsx}',
      './admin/partials/**/*.php'
    ],
    theme: {
      extend: {
        colors: {
          'topsms-orange': '#FF6B00',
        },
      },
    },
    plugins: [],
  }