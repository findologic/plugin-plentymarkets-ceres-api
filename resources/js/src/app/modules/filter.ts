// Filter components may only be loaded on search/navigation pages.

import { VueConstructor } from 'vue';
import TestComponent from '../components/TestComponent.vue';
import ItemListSorting from '../components/itemList/ItemListSorting.vue';
import ItemColorTiles from '../components/itemList/filter/ItemColorTiles.vue';
import ItemCategoryDropdown from '../components/itemList/filter/ItemCategoryDropdown.vue';
import ItemDropdown from '../components/itemList/filter/ItemDropdown.vue';
import ItemRangeSlider from '../components/itemList/filter/ItemRangeSlider.vue';
import FindologicItemFilter from '../components/itemList/filter/FindologicItemFilter.vue';
import ItemFilterTagList from '../components/itemList/filter/ItemFilterTagList.vue';

if (window.flCeresConfig.isSearchPage || window.flCeresConfig.activeOnCatPage) {
    // @ts-ignore
    const plentyVue = Vue as VueConstructor;

    plentyVue.component('test-component', TestComponent);
    plentyVue.component('item-list-sorting', ItemListSorting);
    plentyVue.component('item-color-tiles', ItemColorTiles);
    plentyVue.component('item-category-dropdown', ItemCategoryDropdown);
    plentyVue.component('item-dropdown', ItemDropdown);
    plentyVue.component('item-range-slider', ItemRangeSlider);
    plentyVue.component('findologic-item-filter', FindologicItemFilter);
    plentyVue.component('item-filter-tag-list', ItemFilterTagList);
}
