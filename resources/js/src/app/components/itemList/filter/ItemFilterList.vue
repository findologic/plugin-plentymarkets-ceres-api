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

<style lang="scss">
@import '../../../../../../../vendor/plentymarkets/plugin-ceres/resources/scss/ceres/variables';

.list-controls, .widget-filter-base {
  .card-columns {
    column-gap: 0;
  }
  .main-filters, #filterCollapse {
    position: relative;
    z-index: 0;
    width: 100%;
    margin-left: -15px;
    padding-right: 15px;
    top: 100%;

    .page-content {
      background: $white;
      padding: 2em !important;
      border: 1px solid $gray-200;
      margin: 0 7.5px;

      .filter-toggle {
        position: relative;
        right: 0;
        padding-left: 15px;
        transform: none;
      }
      .card {
        background-color: transparent;
        vertical-align: top;
        border: 0;
        h3 {
          text-transform: uppercase;
          font-size: 1.2rem;
          border-bottom: 1px solid $gray-500;
          padding-bottom: 0.5rem;
          font-weight: bold;
          color: $gray-700;
        }
        .form-check {
          padding-left: 0;

          &:hover {
            background: $gray-100;
            color: $gray-700;
            transition: all 0.1s ease;
            padding-left: 0.5rem;
          }

          .form-check-input {
            &.hidden-xs-up {
              display:none;
            }

            &:checked + label {
              background: $gray-200;
              color: $gray-900;
              padding-left: 1.75rem;

              &::before {
                font-family: "FontAwesome";
                content: "\f046";
                opacity: 1;
                padding-left: 0;
              }

              span::before {
                display: none;
              }
            }

            &:not(:checked) + label {
              width: calc(100% - 3.5rem);
            }

            &:disabled + label {
              cursor: not-allowed;
            }
          }

          .form-check-label {
            padding: 0.5rem 0;
            color: $gray-700;
            display: block;
            transition: all 0.1s ease;

            &::before {
              font-family: "FontAwesome";
              content: "\f046";
              opacity: 0;
              position: absolute;
              left: 0.5rem;
            }
          }
          .filter-badge {
            position: absolute;
            display: inline-block;
            font-size: 0.85rem;
            background: $gray-200;
            padding: 0.1rem 0;
            color: gray;
            right: 0.5rem;
            text-align: center;
            min-width: 3em;
            transform: translateY(-50%);
            top: 1.5em;
          }
        }
      }
    }
  }
  .selected-filters {
    .selected-filter {
      margin: 0 0 0 10px;
    }
  }
}

@media (min-width: 576px) {
  .list-controls .no-main-filters-filter-toggle {
    transform: translateY(-120%);
    -webkit-transform: translateY(-120%);
  }
}
</style>
