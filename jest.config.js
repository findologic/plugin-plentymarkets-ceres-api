module.exports = {
    preset: '@vue/cli-plugin-unit-jest/presets/typescript-and-babel',
    testMatch: [
        '**/resources/js/src/**/*.spec.[jt]s?(x)',
    ],
    verbose: true,
    moduleFileExtensions: [
        'js',
        'ts',
        'json',
        'vue'
    ],
    transform: {
        '.*\\.(vue)$': 'vue-jest',
        '.*\\.(js)$': 'babel-jest',
        '.*\\.(ts)$': 'babel-jest',
    },
    transformIgnorePatterns: [
        '/node_modules/(?!vue-awesome)'
    ]
};
