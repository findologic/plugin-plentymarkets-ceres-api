Vue.component("findologic-item-filter-list", {
    props: {
        facetData: {
            type: Array,
            default: () => []
        },
        currentCategory: {
            type: Array,
            default: () => []
        },
        allowedFacetsTypes: {
            type: Array,
            default: () => []
        },
        showSelectedFiltersCount: {
            type: Boolean,
            default: false
        },
        showCategoryFilter: {
            type: Boolean,
            default: true
        }
    },

    computed: {
        ...Vuex.mapState({
            facets(state)
            {
                if (!this.allowedFacetsTypes.length) {
                    return state.itemList.facets;
                }

                return state.itemList.facets.filter(facet => this.allowedFacetsTypes.includes(facet.id) || this.allowedFacetsTypes.includes(facet.type));
            },
            isLoading: state => state.itemList.isLoading,
            selectedFacets: state => state.itemList.selectedFacets
        })
    },

    created() {
        const facets = this.$store.state.itemList.facets;
        if (!facets && !facets.length) {
            this.$store.commit("addFacets", this.facetData);
        }
    }
});
