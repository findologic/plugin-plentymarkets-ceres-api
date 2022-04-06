declare module '*.vue' {
    import Vue from 'vue';
    export default Vue;

    declare global {
        interface Window {
            ceresTranslate: (key: string) => string;
            $: unknown;
            noUiSlider: {
                create: (element: Element|null, config) => {on: (eventName, callback) => unknown};
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
            SVGInjector: (element: JQuery<HTMLElement>|HTMLElement|Element|null) => unknown;
        }
    }
}
