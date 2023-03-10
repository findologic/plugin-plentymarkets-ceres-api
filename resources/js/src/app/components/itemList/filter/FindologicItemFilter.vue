<template>
  <!-- SSR:template(findologic-item-filter) -->
  <!-- Additionally checking that min and max values aren't the same, because this would be a useless filter. -->
  <div
    v-if="facet.name && ((typeof facet.minValue === 'undefined' && typeof facet.maxValue === 'undefined') || (facet.minValue !== facet.maxValue))"
    class="card"
    :class="[facet.cssClass, 'col-md-' + filtersPerRow]"
  >
    <div
      v-if="facet.id !== 'cat' || shouldShowCategoryFilter"
      class="facet-title"
    >
      <div
        class="h3"
        v-text="facet.name"
      />
      <div
        v-if="selectedValuesCount > 0 && showSelectedFiltersCount"
        class="selected-values-count"
        v-text="selectedValuesCount"
      />
    </div>
    <div v-if="facet.findologicFilterType === 'range-slider'">
      <item-range-slider :facet="facet" />
    </div>
    <div v-else-if="facet.findologicFilterType === 'image'">
      <item-filter-image
        :facet="facet"
        :fallback-image="fallbackImageImageFilter"
      />
    </div>
    <div v-else-if="facet.findologicFilterType === 'color'">
      <div v-if="!facet.noAvailableFiltersText">
        <item-color-tiles
          :facet="facet"
          :fallback-image="fallbackImageColorFilter"
        />
      </div>
      <p
        v-if="facet.noAvailableFiltersText"
        v-text="facet.noAvailableFiltersText"
      />
    </div>
    <div v-else-if="shouldShowCategoryFilter">
      <div v-if="!facet.noAvailableFiltersText">
        <item-category-dropdown
          :current-category="currentCategory"
          :facet="facet"
        />
      </div>
      <p
        v-if="facet.noAvailableFiltersText"
        v-text="facet.noAvailableFiltersText"
      />
    </div>
    <div v-else-if="facet.findologicFilterType === 'select' && (facet.id !== 'cat' || shouldShowCategoryFilter)">
      <div v-if="!facet.noAvailableFiltersText">
        <item-dropdown :facet="facet" />
      </div>
      <p
        v-if="facet.noAvailableFiltersText"
        v-text="facet.noAvailableFiltersText"
      />
    </div>
    <div v-else-if="facet.id !== 'cat' || shouldShowCategoryFilter">
      <div
        v-for="value in facet.values"
        :key="value.id"
        class="form-check"
      >
        <input
          :id="'option-' + value.id"
          class="form-check-input hidden-xs-up"
          type="checkbox"
          :checked="value.selected"
          :disabled="isLoading"
          @change="updateFacet(value)"
        >
        <label
          :for="'option-' + value.id"
          class="form-check-label"
          rel="nofollow"
          v-text="value.name"
        />
        <div
          v-if="value.count"
          class="filter-badge"
          v-text="value.count"
        />
      </div>
    </div>
  </div>
  <!-- /SSR -->
</template>

<script lang="ts" setup>
import { computed, getCurrentInstance } from 'vue';
import {
  Facet,
  FacetValue,
  PlentyVuexStore,
} from '../../../shared/interfaces';
import ItemRangeSlider from './ItemRangeSlider.vue';
import ItemColorTiles from './ItemColorTiles.vue';
import ItemCategoryDropdown from './ItemCategoryDropdown.vue';
import ItemDropdown from './ItemDropdown.vue';
import UrlBuilder from '../../../shared/UrlBuilder';
import ItemFilterImage from './ItemFilterImage.vue';

const props = defineProps({
  template: {
    type: String,
    default: null
  },
  facet: {
    type: Object,
    required: true
  },
  filtersPerRow: {
    type: Number,
    required: true
  },
  fallbackImageColorFilter: {
    type: String,
    default: ''
  },
  fallbackImageImageFilter: {
    type: String,
    default: ''
  },
  showSelectedFiltersCount: {
    type: Boolean,
    default: false
  },
  currentCategory: {
    type: Array,
    default: () => []
  },
  showCategoryFilter: {
    type: Boolean,
    default: true
  }
});

const root = getCurrentInstance()!.proxy;
root.$options.template = props.template || '#vue-item-filter';
const store = root.$store as PlentyVuexStore;
const selectedFacets = computed(() => store.itemList?.selectedFacets);
const isLoading = computed(() => store.itemList?.isLoading || false);

const updateFacet = (facetValue: FacetValue): void => {
  UrlBuilder.updateSelectedFilters(props.facet.value, props.facet.id, facetValue.name);
};

const getSubCategoryValue = (parentCategory: FacetValue, subCategory: Facet): FacetValue => {
  return {
    id: subCategory.id,
    name: parentCategory.name + '_' + subCategory.name
  } as FacetValue;
};

const selectedValuesCount = computed((): number => {
  const facetValues = props.facet.values as FacetValue[];

  const selectedFacets = facetValues.filter((value: FacetValue) => {
    return value.selected;
  });

  return selectedFacets.length;
});

const shouldShowCategoryFilter = computed((): boolean => {
  return props.facet.id === 'cat' && typeof props.showCategoryFilter === 'undefined' ||
      props.facet.id === 'cat' && props.showCategoryFilter;
});
</script>

<style scoped>

</style>
