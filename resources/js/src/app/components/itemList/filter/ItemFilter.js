import url from '../../../mixins/url'

Vue.component("item-filter", {
    mixins: [url],

    delimiters: ["${", "}"],

    props: [
        "template",
        "facet"
    ],

    computed: {
        facets() {
            return this.facet.values.sort((facetA, facetB) => {
                if (facetA.position > facetB.position) {
                    return 1;
                } else if (facetA.position < facetB.position) {
                    return -1;
                } else {
                    return 0;
                }
            });
        },

        ...Vuex.mapState({
            selectedFacets: state => state.itemList.selectedFacets,
            isLoading: state => state.itemList.isLoading
        })
    },

    created() {
        this.$options.template = this.template || "#vue-item-filter";
    },

    methods: {
        updateFacet(facetValue) {
            this.updateSelectedFilters(facetValue.id, facetValue.name);
        },

        isSelected(facetValueId) {
            return this.selectedFacets.findIndex(selectedFacet => selectedFacet.id === facetValueId) > -1;
        },

        getSubCategoryValue(parentCategory, subCategory) {
            return parentCategory.name + '_' + subCategory.name;
        }
    }
});
