declare module 'noUiSlider' {
    import * as nouislider from 'nouislider';

    interface Instance extends HTMLElement {
        noUiSlider: nouislider;
    }
}
