// Base components are always loaded.

import FindologicItemSearch from '../components/itemList/FindologicItemSearch.vue';
import { VueConstructor } from 'vue';

// @ts-ignore
const plentyVue = Vue as VueConstructor;

plentyVue.component('findologic-item-search', FindologicItemSearch);
