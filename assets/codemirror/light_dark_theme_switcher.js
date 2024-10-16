import { Compartment } from "@codemirror/state";
import { EditorView } from "@codemirror/view";

export function lightDarkThemeSwitcher({ lightTheme, darkTheme }) {
    if (!lightTheme) {
        throw new Error(`Parameter 'lightTheme' is required.`);
    }

    if (!darkTheme) {
        throw new Error(`Parameter 'darkTheme' is required.`);
    }

    let initialized = false;
    const themeCompartment = new Compartment();
    const mediaQuery = window.matchMedia("(prefers-color-scheme: dark)");

    return [
        themeCompartment.of(mediaQuery.matches ? darkTheme : lightTheme),
        EditorView.updateListener.of((update) => {
            if (!initialized) {
                initialized = true;
                const mediaQueryChangeHandler = (event) => {
                    update.view.dispatch({
                        effects: themeCompartment.reconfigure(event.matches ? darkTheme : lightTheme),
                    });
                };

                mediaQuery.addEventListener("change", mediaQueryChangeHandler);
                update.view.dom.addEventListener("unload", () => {
                    mediaQuery.removeEventListener("change", mediaQueryChangeHandler);
                });
            }
        }),
    ];
}
