<script lang="ts">
import {
  Facet,
  FacetAware,
  PlentyVuexStore,
  TemplateOverridable,
} from '../../../shared/interfaces';
import { computed, defineComponent } from '@vue/composition-api';

interface ItemColorTilesProps extends TemplateOverridable, FacetAware {
  allFacets: Facet[];
  facets: Facet[];
  allowedFacetsTypes: string[];
}

export default defineComponent({
  name: 'ItemFilterList',
  props: {
    allFacets: {
      type: Array,
      default: () => []
    },
    allowedFacetsTypes: {
      type: Array,
      default: () => []
    }
  },
  setup: (props: ItemColorTilesProps, { root }) => {
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
      selectedFacets
    };
  }
});
</script>

<style scoped>

</style>
