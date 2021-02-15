import type {Config} from '@jest/types';

const config: Config.InitialOptions = {
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

export default config;
