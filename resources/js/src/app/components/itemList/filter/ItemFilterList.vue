<template>
  <!-- SSR:template(findologic-item-filter-list) -->
  <div class="findologic-filter-wrapper wt"
       :class="{'mb-5': facets.some(e => e.isMain === true)}"
       v-show="facets && facets.length > 0"
  >
    <div class="ml-0 main-filters" v-if="facets.some(e => e.isMain === true)">
        <div class="container-max component-loading page-content" :class="{ 'isLoading': isLoading }">
          <div class="card-columns row">
            <client-only>
              <div class="w-100">
                <findologic-item-filter
                    v-bind:filtersPerRow="filtersPerRow"
                    v-bind:fallbackImageColorFilter="fallbackImageColorFilter"
                    v-bind:fallbackImageImageFilter="fallbackImageImageFilter"
                    v-bind:show-selected-filters-count="showSelectedFiltersCount"
                    v-for='facet in facets'
                    v-if="facet.id === 'cat' ? showCategoryFilter && facet.isMain : facet.isMain"
                    :current-category='currentCategory'
                    :show-category-filter='showCategoryFilter'
                    :facet='facet'
                    :key='facet.id'
                />
              </div>
            </client-only>
          </div>
          <div class="row">
            <a class="btn btn-link filter-toggle" data-toggle="collapse" href="#filterCollapse"
               aria-controls="filterCollapse">
              <i class="fa fa-sliders default-float"
                 aria-hidden="true"></i> {{ TranslationService.translate('Findologic::Template.itemFilter') }}
            </a>
          </div>
        </div>
    </div>
  </div>
  <!-- /SSR -->
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
import TranslationService from '../../../shared/TranslationService';
import FindologicItemFilter from './FindologicItemFilter.vue';

interface ItemFilterListProps extends TemplateOverridable, FacetAware {
  allFacets: Facet[];
  facets: Facet[];
  allowedFacetsTypes: string[];
  currentCategory: CategoryFacet[];
  showCategoryFilter: boolean;
  filtersPerRow: number;
  fallbackImageColorFilter: string;
  fallbackImageImageFilter: string;
  showSelectedFiltersCount: boolean;
}

export default defineComponent({
  name: 'ItemFilterList',
  components: {
    'findologic-item-filter': FindologicItemFilter,
  },
  props: {
    allFacets: {
      type: Array,
      default: () => []
    },
    allowedFacetsTypes: {
      type: Array,
      default: () => []
    },
    currentCategory: {
      type: Array,
      default: () => []
    },
    showCategoryFilter: {
      type: Boolean,
      default: true
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
  },
  setup: (props: ItemFilterListProps, { root }) => {
    const store = root.$store as PlentyVuexStore;

    const facets = computed(() => {
      if (props.allowedFacetsTypes.length === 0) {
        return props.allFacets;
      }

      return store.state.itemList.facets.filter((facet: Facet) => {
        return props.allowedFacetsTypes.includes(facet.id) || props.allowedFacetsTypes.includes(facet?.type ?? '');
      });
    });
    const isLoading = computed(() => store.state.itemList.isLoading);
    const selectedFacets = computed(() => store.state.itemList.selectedFacets);

    store.commit('addFacets', facets.value);

    return {
      facets,
      isLoading,
      selectedFacets,
      TranslationService
    };
  }
});
</script>

<style scoped>

</style>
