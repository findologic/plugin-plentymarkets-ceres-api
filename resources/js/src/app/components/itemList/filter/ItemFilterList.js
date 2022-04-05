Vue.component("findologic-item-filter-list", {
    props: {
        template:
        {
            type: String,
            default: "#findologic-item-filter-list"
        },
        facetData: {
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
        this.$options.template = this.template || "#vue-item-filter-list";
        this.$store.commit("addFacets", this.facetData);
    }
});
