declare module 'jquery' {
    interface Accordion extends HTMLElement {
        collapse: (action: string) => void;
    }
}
