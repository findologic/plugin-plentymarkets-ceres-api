<template>
  <!-- SSR:template(findologic-item-filter) -->
  <!-- Additionally checking that min and max values aren't the same, because this would be a useless filter. -->
  <div
    v-if="facet.name && ((typeof facet.minValue === 'undefined' && typeof facet.maxValue === 'undefined') || (facet.minValue !== facet.maxValue))"
    class="card"
    :class="[facet.cssClass, 'col-md-' + filtersPerRow]"
  >
    <div class="facet-title">
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
    <div v-else-if="facet.id === 'cat'">
      <div v-if="!facet.noAvailableFiltersText">
        <div v-if="facet.findologicFilterType === 'select'">
          <item-category-dropdown
            v-if="facet.findologicFilterType === 'select'"
            :facet="facet"
          />
        </div>
        <div
          v-for="value in facet.values"
          v-else
          :key="value.id"
          class="form-check"
        >
          <div class="category-container">
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
              class="filter-badge"
              v-text="value.count"
            />
          </div>
          <div v-if="value.selected">
            <div
              v-if="value.items.length > 0"
              class="sub-category-container"
            >
              <div
                v-for="subCategory in value.items"
                :key="subCategory.id"
                class="form-check"
              >
                <input
                  :id="'option-' + subCategory.id"
                  class="form-check-input hidden-xs-up"
                  type="checkbox"
                  :checked="subCategory.selected"
                  :disabled="isLoading"
                  @change="updateFacet(getSubCategoryValue(value, subCategory))"
                >
                <label
                  :for="'option-' + subCategory.id"
                  class="form-check-label"
                  rel="nofollow"
                  v-text="subCategory.name"
                />
                <div
                  class="filter-badge"
                  v-text="subCategory.count"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
      <p
        v-if="facet.noAvailableFiltersText"
        v-text="facet.noAvailableFiltersText"
      />
    </div>
    <div v-else-if="facet.findologicFilterType === 'select'">
      <div v-if="!facet.noAvailableFiltersText">
        <item-dropdown :facet="facet" />
      </div>
      <p
        v-if="facet.noAvailableFiltersText"
        v-text="facet.noAvailableFiltersText"
      />
    </div>
    <div v-else>
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
          class="filter-badge"
          v-text="value.count"
        />
      </div>
    </div>
  </div>
  <!-- /SSR -->
</template>

<script lang="ts">
import { computed, defineComponent } from '@vue/composition-api';
import {
  Facet,
  FacetAware,
  FacetValue,
  PlentyVuexStore,
  TemplateOverridable,
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
}

export default defineComponent({
  name: 'FindologicItemFilter',
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
    }
  },
  components: {
    'item-range-slider': ItemRangeSlider,
    'item-color-tiles': ItemColorTiles,
    'item-category-dropdown': ItemCategoryDropdown,
    'item-dropdown': ItemDropdown,
    'item-filter-image': ItemFilterImage
  },
  setup: (props: ItemFilterProps, { root }) => {
    root.$options.template = props.template || '#vue-item-filter';
    const store = root.$store as PlentyVuexStore;

    const selectedFacets = computed(() => store.itemList?.selectedFacets);
    const isLoading = computed(() => store.itemList?.isLoading || false);

    const updateFacet = (facetValue: FacetValue): void => {
      UrlBuilder.updateSelectedFilters(props.facet, props.facet.id, facetValue.name);
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

    return {
      selectedFacets,
      isLoading,
      updateFacet,
      getSubCategoryValue,
      selectedValuesCount
    };
  }
});
</script>

<style scoped>

</style>
