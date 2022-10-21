import Vue from 'vue';

class TranslationService {
    public translate(keypath: string): string {
        if (typeof window === 'undefined') {
            return Vue.prototype.$translate(keypath);
        }

        return window.ceresTranslate(keypath);
    }
}

export default new TranslationService();
