module.exports = {
  root: true,
  env: {
    jquery: true,
    es2021: true
  },
  'extends': [
    'plugin:vue/essential',
    'eslint:recommended',
    '@vue/typescript/recommended',
    'plugin:vue/recommended'
  ],
  parserOptions: {
    ecmaVersion: 2020
  },
  ignorePatterns: ['resources/js/dist/*'],
  rules: {
    'no-console': process.env.NODE_ENV === 'production' ? 'warn' : 'off',
    'no-debugger': process.env.NODE_ENV === 'production' ? 'warn' : 'off',
    '@typescript-eslint/ban-ts-ignore': 'off',
    'semi': ['error', 'always'],
    'quotes': ['error', 'single'],
    'object-curly-spacing': ['error', 'always']
  },
  overrides: [
    {
      files: [
        './resources/js/src/**/*.spec.{j,t}s?(x)'
      ],
      env: {
        jest: true
      }
    }
  ]
}
