// Base components are always loaded.

import FindologicItemSearch from '../components/itemList/FindologicItemSearch.vue';
import { VueConstructor } from 'vue';

// eslint-disable-next-line
// @ts-ignore
const plentyVue = Vue as VueConstructor;

plentyVue.component('FindologicItemSearch', FindologicItemSearch);
