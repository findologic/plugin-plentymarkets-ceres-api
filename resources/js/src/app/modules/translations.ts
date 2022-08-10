import VueI18n from 'vue-i18n';
import messages from '../../../../lang/index';

const i18n = new VueI18n({
    locale: document.documentElement.lang,
    fallbackLocale: 'en',
    messages,
    silentTranslationWarn: true
});

const translate = (key: string): string => {
    if (!key) {
        return '';
    }
    return i18n.t(key) as string;
};

export { translate };
