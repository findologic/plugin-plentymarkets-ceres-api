// Vue plugins.

import { VueConstructor } from 'vue';
import VueCompositionAPI from '@vue/composition-api';

// eslint-disable-next-line
// @ts-ignore
const plentyVue = Vue as VueConstructor;

const plugins = [
    VueCompositionAPI,
];

plugins.forEach((plugin) => {
    plentyVue.use(plugin);
});
