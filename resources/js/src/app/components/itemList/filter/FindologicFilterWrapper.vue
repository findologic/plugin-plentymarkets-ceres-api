<template>
  <client-only>
    <div class="findologic-filter-wrapper"
         :class="{'mb-5': facets.some(e => e.isMain === true)}"
         v-show="facets && facets.length > 0">
      <div class="ml-0 main-filters">
        <div class="container-max component-loading page-content" :class="{ 'isLoading': isLoading }">
          <div class="card-columns row">
            <client-only>
              <div class="w-100">
                <!-- SSR:template(findologic-item-filter) -->
                <findologic-item-filter template-override="#vue-findologic-item-filter"
                                        v-for="facet in facets"
                                        :facet="facet"
                                        :key="facet.id"/>.
                <!-- /SSR -->
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

interface FindologicFilterWrapperProps extends TemplateOverridable, FacetAware {
  facets: Facet[];
}

export default defineComponent({
  name: 'FindologicFilterWrapper',
  components: {
    'findologic-item-filter': FindologicItemFilter,
  },
  props: {
    facets: {
      type: Array,
      default: () => []
    }
  },
  setup: (props: FindologicFilterWrapperProps, { root }) => {
    const store = root.$store as PlentyVuexStore;
    const isLoading = computed(() => store.state.itemList.isLoading);

    return {
      isLoading,
    };
  }
});
</script>
