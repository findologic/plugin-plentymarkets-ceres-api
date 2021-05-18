class TranslationService {
    public translate(keypath: string): string {
        return window.ceresTranslate(keypath);
    }
}

export default new TranslationService();
