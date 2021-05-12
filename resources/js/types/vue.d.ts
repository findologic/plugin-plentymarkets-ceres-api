declare module '*.vue' {
    import Vue from 'vue';
    export default Vue;

    declare global {
        interface Window {
            ceresTranslate: (key: string) => {};
            noUiSlider: {
                create: (element: Element|null, config) => {on: (eventName, callback) => {}};
            };
        }
    }
}
