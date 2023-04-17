module.exports = {
    "testEnvironment": "jsdom",
    testMatch: [
        "**/resources/js/src/**/*.spec.[jt]s?(x)",
    ],
    verbose: true,
    moduleFileExtensions: [
        'js',
        'ts',
        'json',
        'vue'
    ],
    transform: {
        '.*\\.(vue)$': '@vue/vue2-jest',
        '.*\\.(js)$': 'babel-jest',
        '.*\\.(ts)$': 'babel-jest',
    },
    transformIgnorePatterns: [
        '/node_modules/(?!vue-awesome)'
    ],
    moduleNameMapper: { "\\.css$": "<rootDir>/resources/js/src/assets/css/__mocks__/styleMock.js" }
};
