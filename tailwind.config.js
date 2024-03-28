/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
  ],
  theme: {
    extend: {},
  },
  plugins: [
    require('@tailwindcss/typography'),
  ],
  safelist: [
      'custom-container',
      'custom-container__title',
      'custom-container--info',
      'custom-container--tip',
      'custom-container--warning',
      'custom-container--danger',
  ]
}
