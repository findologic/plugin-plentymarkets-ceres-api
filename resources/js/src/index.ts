/// <reference path="../declarations/globals.d.ts" />

import './../../scss/findologic.scss'

// TODO: Split up into subcomponents
import './app/components/itemList/filter/ItemFilter'
import './app/components/itemList/filter/ItemFilterPrice.js'
import './app/components/itemList/filter/ItemFilterTagList.js'
import './app/components/itemList/filter/ItemRangeSlider.js'
import './app/components/itemList/filter/ItemColorTiles.js'
import './app/components/itemList/filter/ItemDropdown.js'
import './app/components/itemList/filter/ItemCategoryDropdown.js'
import './app/components/itemList/ItemsPerPage.js'
import './app/components/itemList/Pagination.js'
import './app/components/itemList/ItemSearch.js'
import './app/directives/navigation/renderCategory.js'

// Testing Vue
import TestComponent from './app/components/TestComponent.vue';
import ItemListSorting from './app/components/itemList/ItemListSorting.vue';

Vue.component('test-component', TestComponent)
Vue.component('item-list-sorting', ItemListSorting)
