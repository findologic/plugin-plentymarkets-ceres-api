module.exports = {
    moduleFileExtensions: [
        'js',
        'vue'
    ],
    transform: {
        "^.+\\.jsx?$": "babel-jest",
        '^.+\\.vue$': 'vue-jest',
    },
    transformIgnorePatterns: [
        '/node_modules/'
    ],
    globals: {
        App: {
            config: {
                search: {
                    forwardToSingleItem: true
                }
            },
            defaultLanguage: 'de',
            language: 'en',
        },
        $store: {
            commit: (state, parameters) => {}
        }
    },
};