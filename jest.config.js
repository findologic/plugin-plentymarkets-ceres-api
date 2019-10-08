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
    setupFiles: [
        './jest.setup.js'
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
        Vuex: {
            mapState: function(options) {}
        }
    },
};