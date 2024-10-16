import { Compartment } from "@codemirror/state";
import { EditorView } from "@codemirror/view";

export function lightDarkThemeSwitcher({ lightTheme, darkTheme }) {
    if (!lightTheme) {
        throw new Error(`Parameter 'lightTheme' is required.`);
    }

    if (!darkTheme) {
        throw new Error(`Parameter 'darkTheme' is required.`);
    }

    const themeCompartment = new Compartment();

    return [
        themeCompartment.of(
            window.matchMedia("(prefers-color-scheme: dark)").matches
                ? darkTheme
                : lightTheme,
        ),

        EditorView.domEventHandlers({
            window: {
                matchMedia: (event) => {
                    if (event.media === "(prefers-color-scheme: dark)") {
                        event.target.dispatch({
                            effects: themeCompartment.reconfigure(
                                event.matches ? darkTheme : lightTheme,
                            ),
                        });
                    }
                },
            },
        }),
    ];
}
