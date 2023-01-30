<template>
  <client-only>
    <div
      class="findologic-filter-wrapper"
      :class="{ 'mb-5': facets.some((e) => e.isMain === true) }"
      v-show="facets && facets.length > 0"
    >
      <div class="ml-0 main-filters" v-if="facets.some(e => e.isMain === true)">
        <div
          class="container-max component-loading page-content"
          :class="{ isLoading: isLoading }"
        >
          <div class="card-columns row">
            <client-only>
              <div class="w-100">
                <findologic-item-filter
                  v-for="facet in mainFilters"
                  :facet="facet"
                  :key="facet.id"
                  :filtersPerRow="filtersPerRow"
                  :fallbackImageColorFilter="fallbackImageColorFilter"
                  :fallbackImageImageFilter="fallbackImageImageFilter"
                  :current-category="currentCategory"
                  :show-category-filter="showCategoryFilter"
                />
              </div>
            </client-only>
          </div>
        </div>
      </div>

      <div v-else class="ml-0">
        <div class="container-max component-loading" :class="{ 'isLoading': isLoading }">
            <div class="row">
                <a class="btn btn-link filter-toggle no-main-filters-filter-toggle" data-toggle="collapse"
                    href="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                    <i class="fa fa-sliders default-float"
                        aria-hidden="true"></i> {{ TranslationService.translate("Findologic::Template.noMainFiltersItemFilter") }}
                </a>
            </div>
        </div>
      </div>

      <div class="ml-0 filter-collapse collapse" id="filterCollapse">
        <div class="container-max component-loading page-content mb-5" :class="{ 'isLoading': isLoading }">
            <div class="card-columns row">
                <client-only>
                    <div class="w-100">
                        <findologic-item-filter
                            v-for="facet in secondaryFilters"
                            :facet="facet"
                            :key="facet.id"
                            :filtersPerRow="filtersPerRow"
                            :fallbackImageColorFilter="fallbackImageColorFilter"
                            v-bind:fallbackImageImageFilter="fallbackImageImageFilter"
                            v-bind:show-selected-filters-count="1"
                            :current-category="currentCategory"
                            :show-category-filter="showCategoryFilter"
                        ></findologic-item-filter>
                    </div>
                </client-only>
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
    }
  },

  setup: (props: FindologicFilterWrapperProps, { root }) => {
    const store = root.$store as PlentyVuexStore;
    const isLoading = computed(() => store.state.itemList.isLoading);
    const mainFilters = computed((): Facet[] => props.facets.filter((filter: Facet) => filter.id === 'cat' ? props.showCategoryFilter && filter.isMain : filter.isMain));
    const secondaryFilters = computed((): Facet[] => props.facets.filter((filter: Facet) => !filter.isMain));

    return {
      isLoading,
      TranslationService,
      mainFilters,
      secondaryFilters
    };
  },
});
</script>
