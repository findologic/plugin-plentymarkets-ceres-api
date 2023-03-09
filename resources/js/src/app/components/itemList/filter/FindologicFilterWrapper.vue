<template>
  <client-only>
    <div
      v-show="facets && facets.length > 0"
      class="findologic-filter-wrapper"
      :class="{ 'mb-5': facets.some((e) => e.isMain === true) }"
    >
      <div
        v-if="facets.some(e => e.isMain === true)"
        class="ml-0 main-filters"
      >
        <div
          class="container-max component-loading page-content"
          :class="{ isLoading: isLoading }"
        >
          <div class="card-columns row">
            <client-only>
              <div class="w-100">
                <findologic-item-filter
                  v-for="facet in mainFacets"
                  :key="facet.id"
                  :facet="facet"
                  :filters-per-row="filtersPerRow"
                  :fallback-image-color-filter="fallbackImageColorFilter"
                  :fallback-image-image-filter="fallbackImageImageFilter"
                  :current-category="currentCategory"
                  :show-category-filter="showCategoryFilter"
                />
              </div>
            </client-only>
          </div>
        </div>
      </div>

      <div v-if="secondaryFacets.length">
        <div class="ml-0">
          <div
            class="container-max component-loading"
            :class="{ 'isLoading': isLoading }"
          >
            <div class="row">
              <a
                class="btn btn-link filter-toggle no-main-filters-filter-toggle"
                data-toggle="collapse"
                href="#filterCollapse"
                aria-expanded="false"
                aria-controls="filterCollapse"
              >
                <i
                  class="fa fa-sliders default-float"
                  aria-hidden="true"
                /> {{ TranslationService.translate("Findologic::Template.noMainFiltersItemFilter") }}
              </a>
            </div>
          </div>
        </div>
  
        <div
          id="filterCollapse"
          class="ml-0 filter-collapse collapse"
        >
          <div
            class="container-max component-loading page-content mb-5"
            :class="{ 'isLoading': isLoading }"
          >
            <div class="card-columns row">
              <client-only>
                <div class="w-100">
                  <findologic-item-filter
                    v-for="facet in secondaryFacets"
                    :key="facet.id"
                    :facet="facet"
                    :filters-per-row="filtersPerRow"
                    :fallback-image-color-filter="fallbackImageColorFilter"
                    :fallback-image-image-filter="fallbackImageImageFilter"
                    :show-selected-filters-count="showSelectedFiltersCount"
                    :current-category="currentCategory"
                    :show-category-filter="showCategoryFilter"
                  />
                </div>
              </client-only>
            </div>
          </div>
        </div>
      </div>
    </div>
  </client-only>
</template>

<script lang="ts">
import {
  CategoryFacet,
  Facet,
  FacetAware,
  PlentyVuexStore,
  TemplateOverridable,
} from '../../../shared/interfaces';
import { computed, defineComponent } from '@vue/composition-api';
import FindologicItemFilter from './FindologicItemFilter.vue';
import type { PropType } from '@vue/composition-api';
import TranslationService from '../../../shared/TranslationService';

interface FindologicFilterWrapperProps extends TemplateOverridable, FacetAware {
  facets: Facet[];
  showCategoryFilter: boolean
}

export default defineComponent({
  name: 'FindologicFilterWrapper',
  components: {
    'findologic-item-filter': FindologicItemFilter,
  },
  props: {
    facets: {
      type: Array as PropType<Array<Facet>>,
      default: () => [],
    },
    filtersPerRow: {
      type: Number,
      required: true,
    },
    fallbackImageColorFilter: {
      type: String,
      default: '',
    },
    fallbackImageImageFilter: {
      type: String,
      default: '',
    },
    currentCategory: {
      type: Array,
      default: () => []
    },
    showCategoryFilter: {
      type: Boolean,
      default: true
    },
    showSelectedFiltersCount: {
      type: Boolean,
      default: true
    }
  },

  setup: (props: FindologicFilterWrapperProps, { root }) => {
    const store = root.$store as PlentyVuexStore;
    const isLoading = computed(() => store.state.itemList.isLoading);
    const mainFacets = computed((): Facet[] => props.facets.filter((facet: Facet) => facet.id === 'cat' ? props.showCategoryFilter && facet.isMain : facet.isMain));
    const secondaryFacets = computed((): Facet[] => props.facets.filter((facet: Facet) => !facet.isMain));

    return {
      isLoading,
      TranslationService,
      mainFacets,
      secondaryFacets
    };
  },
});
</script>
