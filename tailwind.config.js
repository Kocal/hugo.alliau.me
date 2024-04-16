/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
  ],
  theme: {
      extend: {
          colors: {
              primary: {
                  '50': '#edf8ff',
                  '100': '#d6edff',
                  '200': '#b5e0ff',
                  '300': '#83cfff',
                  '400': '#48b3ff',
                  '500': '#1e8eff',
                  '600': '#066dff',
                  '700': '#0057fa',
                  '800': '#0844c5',
                  '900': '#0d3e9b',
                  '950': '#0e275d',
              },
          },
          typography: ({ theme }) => ({
              DEFAULT: {
                  css: {
                      // We don't want to apply any styles to the `pre` and `pre code` tags, as we want to
                      // handle the styling ourselves.
                      pre: null,
                      'pre code': null,
                      'code': {
                          fontWeight: '500',
                      },
                      '--tw-prose-bullets': theme('colors.gray[500]'),
                      '--tw-prose-links': theme('colors.primary[700]')
                  },
              },
          }),
      },
  },
  plugins: [
    require('@tailwindcss/typography'),
  ],
  safelist: [
      'not-prose',
      'custom-container',
      'custom-container__title',
      'custom-container--info',
      'custom-container--tip',
      'custom-container--warning',
      'custom-container--danger',
      'table-of-contents',
      'heading-permalink',
  ]
}
