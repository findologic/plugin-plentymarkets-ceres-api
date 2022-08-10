import Vue from 'vue';
import { translate } from '../modules/translations';

class TranslationService {
    public ceresTranslate(keypath: string): string {
        if (typeof window === 'undefined') {
            return Vue.prototype.$translate(keypath);
        }

        return window.ceresTranslate(keypath);
    }

    public flTranslate(key: string, prefix = 'findologic'): string {
        return translate(`${prefix}.${key}`);
    }
}

export default new TranslationService();
