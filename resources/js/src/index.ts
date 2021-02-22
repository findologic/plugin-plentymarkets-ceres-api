import './../../scss/findologic.scss'

// TODO: Split up into subcomponents
import './app/components/itemList/filter/ItemFilter'
import './app/components/itemList/filter/ItemFilterPrice.js'
import './app/components/itemList/filter/ItemFilterTagList.js'
import './app/components/itemList/filter/ItemRangeSlider.js'
// import './app/components/itemList/filter/ItemColorTiles.js'
import './app/components/itemList/filter/ItemDropdown.js'
import './app/components/itemList/filter/ItemCategoryDropdown.js'
import './app/components/itemList/ItemsPerPage.js'
import './app/components/itemList/Pagination.js'
import './app/components/itemList/ItemSearch.js'
import './app/directives/navigation/renderCategory.js'

// Testing Vue
import TestComponent from './app/components/TestComponent.vue';
import ItemListSorting from './app/components/itemList/ItemListSorting.vue';
import ItemColorTiles from './app/components/itemList/filter/ItemColorTiles.vue';

// @ts-ignore
const plentyVue = Vue;

plentyVue.component('test-component', TestComponent)
plentyVue.component('item-list-sorting', ItemListSorting)
plentyVue.component('item-color-tiles', ItemColorTiles)
