<template>
  <div
    v-if="facet.name && ((typeof facet.min === 'undefined' && typeof facet.max === 'undefined') || (facet.min !== facet.max))"
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
    <div v-if="facet.findologicFilterType === 'rangeSliderFilter'">
      <item-range-slider :facet="facet" />
    </div>
    <div v-else-if="facet.findologicFilterType === 'vendorImageFilter'">
      <item-filter-image
        :facet="facet"
        :fallback-image="fallbackImageImageFilter"
      />
    </div>
    <div v-else-if="facet.findologicFilterType === 'colorPickerFilter'">
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
    <div v-else-if="facet.findologicFilterType === 'selectFilter' && (facet.id !== 'cat' || shouldShowCategoryFilter)">
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
          v-text="value.translated.name"
        />
        <div
          v-if="value.frequency"
          class="filter-badge"
          v-text="value.frequency"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { computed, defineComponent } from '@vue/composition-api';
import {
  CategoryFacet,
  Facet,
  FacetAware,
  FacetValue,
  PlentyVuexStore,
  TemplateOverridable
} from '../../../shared/interfaces';
import ItemRangeSlider from './ItemRangeSlider.vue';
import ItemColorTiles from './ItemColorTiles.vue';
import ItemCategoryDropdown from './ItemCategoryDropdown.vue';
import ItemDropdown from './ItemDropdown.vue';
import UrlBuilder from '../../../shared/UrlBuilder';
import ItemFilterImage from './ItemFilterImage.vue';

interface ItemFilterProps extends TemplateOverridable, FacetAware {
  filtersPerRow: number;
  fallbackImageColorFilter: string;
  fallbackImageImageFilter: string;
  showSelectedFiltersCount: boolean;
  currentCategory: CategoryFacet[];
  showCategoryFilter: boolean;
}

export default defineComponent({
  name: 'FindologicItemFilter',
  components: {
    'item-range-slider': ItemRangeSlider,
    'item-color-tiles': ItemColorTiles,
    'item-category-dropdown': ItemCategoryDropdown,
    'item-dropdown': ItemDropdown,
    'item-filter-image': ItemFilterImage
  },
  props: {
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
  },
  setup: (props: ItemFilterProps, { root }) => {
    root.$options.template = props.template || '#vue-item-filter';
    const store = root.$store as PlentyVuexStore;

    const selectedFacets = computed(() => store.itemList?.selectedFacets);
    const isLoading = computed(() => store.itemList?.isLoading || false);

    const updateFacet = (facetValue: FacetValue): void => {
      UrlBuilder.updateSelectedFilters(props.facet, props.facet.id, facetValue.translated.name);
    };

    const getSubCategoryValue = (parentCategory: FacetValue, subCategory: Facet): FacetValue => {
      return {
        id: subCategory.id,
        name: parentCategory.translated.name + '_' + subCategory.translated.name
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

    return {
      selectedFacets,
      isLoading,
      updateFacet,
      getSubCategoryValue,
      selectedValuesCount,
      shouldShowCategoryFilter
    };
  }
});
</script>

<style scoped>

</style>
