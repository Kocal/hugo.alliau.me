/** @type {import('tailwindcss').Config} */
module.exports = {
    content: ["./assets/**/*.js", "./templates/**/*.html.twig"],
    theme: {
        extend: {
            screens: {
                print: { raw: "print" },
                screen: { raw: "screen" },
            },
            colors: {
                primary: {
                    50: "#eff8ff",
                    100: "#dbeefe",
                    200: "#c0e1fd",
                    300: "#94d0fc",
                    400: "#62b5f8",
                    500: "#3d96f4",
                    600: "#2174e8",
                    700: "#1f62d6",
                    800: "#2050ad",
                    900: "#1f4689",
                    950: "#182b53",
                },
            },
            typography: ({ theme }) => ({
                DEFAULT: {
                    css: {
                        // We don't want to apply any styles to the `pre` and `pre code` tags, as we want to
                        // handle the styling ourselves.
                        pre: null,
                        "pre code": null,
                        code: {
                            fontWeight: 600,
                            backgroundColor: theme("colors.gray.100"),
                            color: theme("colors.gray.800"),
                            padding: `${theme('spacing.1')} ${theme('spacing.2')}`,
                            fontSize: '85%',
                            borderRadius: theme('borderRadius.md')
                        },
                        'code::before': null,
                        'code::after': null,
                        img: {
                            marginLeft: 'auto',
                            marginRight: 'auto',
                        },
                        "--tw-prose-bullets": theme("colors.gray[500]"),
                        "--tw-prose-links": theme("colors.primary[700]"),
                    },
                },
            }),
        },
    },
    plugins: [require("@tailwindcss/typography")],
    safelist: [
        "not-prose",
        "custom-container",
        "custom-container__title",
        "custom-container--info",
        "custom-container--tip",
        "custom-container--warning",
        "custom-container--danger",
        "table-of-contents",
        "heading-permalink",
    ],
};
