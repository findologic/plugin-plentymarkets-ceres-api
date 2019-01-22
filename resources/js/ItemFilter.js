import url from './mixins/url'

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
            if (facetValue.hasOwnProperty('name')) {
                facetValue = facetValue.name;
            }

            this.updateSelectedFilters(this.facet.id, facetValue);
        },

        isSelected(facetValue) {
            if (facetValue.hasOwnProperty('name')) {
                facetValue = facetValue.name;
            }

            return this.isValueSelected(this.facet.id, facetValue);
        },

        getSubCategoryValue(parentCategory, subCategory) {
            return parentCategory.name + '_' + subCategory.name;
        }
    }
});
