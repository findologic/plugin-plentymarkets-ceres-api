// Filter components may only be loaded on search/navigation pages.

import { VueConstructor } from 'vue';
import TestComponent from '../components/TestComponent.vue';
import ItemListSorting from '../components/itemList/ItemListSorting.vue';
import ItemColorTiles from '../components/itemList/filter/ItemColorTiles.vue';
import ItemCategoryDropdown from '../components/itemList/filter/ItemCategoryDropdown.vue';
import ItemDropdown from '../components/itemList/filter/ItemDropdown.vue';
import ItemRangeSlider from '../components/itemList/filter/ItemRangeSlider.vue';
import FindologicItemFilter from '../components/itemList/filter/FindologicItemFilter.vue';
import FindologicItemFilterTagList from '../components/itemList/filter/FindologicItemFilterTagList.vue';
import ItemFilterImage from '../components/itemList/filter/ItemFilterImage.vue';
import ItemFilterList from '../components/itemList/filter/ItemFilterList.vue';
import SearchTestComponent from '../components/SearchTestComponent.vue';
import SmartDidYouMean from '../components/SmartDidYouMean.vue';
import PromotionComponent from '../components/PromotionComponent.vue';
import FindologicFilterWrapper from '../components/itemList/filter/FindologicFilterWrapper.vue';


function isPageWhereComponentsShouldBeLoaded(): boolean {
    if (typeof window === 'undefined') {
        return true;
    }

    return window.flCeresConfig.isSearchPage || window.flCeresConfig.activeOnCatPage;
}

if (isPageWhereComponentsShouldBeLoaded()) {
    // eslint-disable-next-line
    // @ts-ignore
    const plentyVue = Vue as VueConstructor;

    plentyVue.component('TestComponent', TestComponent);
    plentyVue.component('ItemListSorting', ItemListSorting);
    plentyVue.component('ItemColorTiles', ItemColorTiles);
    plentyVue.component('ItemCategoryDropdown', ItemCategoryDropdown);
    plentyVue.component('ItemDropdown', ItemDropdown);
    plentyVue.component('ItemRangeSlider', ItemRangeSlider);
    plentyVue.component('ItemFilterImage', ItemFilterImage);
    plentyVue.component('FindologicItemFilterList', ItemFilterList);
    plentyVue.component('FindologicItemFilter', FindologicItemFilter);
    plentyVue.component('FindologicItemFilterTagList', FindologicItemFilterTagList);
    plentyVue.component('SearchTestComponent', SearchTestComponent);
    plentyVue.component('SmartDidYouMean', SmartDidYouMean);
    plentyVue.component('PromotionComponent', PromotionComponent);
    plentyVue.component('FindologicFilterWrapper', FindologicFilterWrapper);
}
