declare module '*.vue' {
    import Vue from 'vue';
    export default Vue;

    declare global {
        interface Window {
            ceresTranslate: (key: string) => string;
            noUiSlider: {
                create: (element: Element|null, config) => {on: (eventName, callback) => {}};
            };
            App: {
                defaultLanguage: string;
                language: string;
                config: {
                    search: {
                        forwardToSingleItem: boolean;
                    };
                };
            };
            flCeresConfig: {
                isSearchPage: boolean;
                activeOnCatPage: boolean;
            };
        }
    }
}
