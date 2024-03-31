/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
  ],
  theme: {
      extend: {
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
