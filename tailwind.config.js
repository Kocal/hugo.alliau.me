/** @type {import('tailwindcss').Config} */
module.exports = {
    content: ["./assets/**/*.js", "./templates/**/*.html.twig"],
    theme: {
        extend: {
            fontFamily: {
                sans: [
                    '"Inter var", "Inter Variable Fallback", "ui-sans-serif", "system-ui", "sans-serif", "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI", "Symbol", "Noto Color Emoji"',
                    {
                        fontFeatureSettings: '"cv02","cv03","cv04","cv11"',
                    },
                ],
            },
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
                bsky: "#0a7aff",
                github: "#181717",
                twitter: "#00acee",
                linkedin: "#0077b5",
                mastodon: "#5c4adf",
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
                            backgroundColor: "rgba(0, 0, 0, 0.15)",
                            padding: `${theme("spacing.1")} ${theme("spacing.2")}`,
                            fontSize: "85%",
                            borderRadius: theme("borderRadius.md"),
                            overflowWrap: "break-word",
                        },
                        "code::before": null,
                        "code::after": null,
                        img: {
                            marginLeft: "auto",
                            marginRight: "auto",
                        },
                        "--tw-prose-code": theme("colors.gray.800"),
                        "--tw-prose-code-bg": theme("colors.gray.100"),
                        "--tw-prose-invert-code": theme("colors.gray.100"),
                        "--tw-prose-invert-code-bg": theme("colors.gray.700"),
                        "--tw-prose-bullets": theme("colors.gray[500]"),
                        "--tw-prose-links": theme("colors.primary[700]"),

                        ".CustomContainer, .Terminal": {
                            marginTop: theme("spacing.2"),
                            marginBottom: theme("spacing.2"),
                        },
                    },
                },
                invert: {
                    css: {
                        "--tw-prose-code": "var(--tw-prose-invert-code)",
                        "--tw-prose-code-bg": "var(--tw-prose-invert-code-bg)",
                    },
                },
            }),
        },
    },
    plugins: [require("@tailwindcss/typography")],
    safelist: [
        "not-prose",
        "table-of-contents",
        "heading-permalink",
        { pattern: /^Terminal/ },
        { pattern: /^CustomContainer/ },
    ],
};
