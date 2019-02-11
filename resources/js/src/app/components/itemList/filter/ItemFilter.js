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
            this.updateSelectedFilters(this.facet.id, facetValue.name);
        },

        isSelected(facetValueId) {
            let facetValue = this.facet.values.filter((value) => value.id === facetValueId);

            // Only the category filter can have nested values.
            if (facetValue.length === 0 && this.facet.id === 'cat') {
                for (let i in this.facet.values) {
                    if (this.facet.values[i].hasOwnProperty('items') === false) {
                        continue;
                    }

                    facetValue = this.facet.values[i].items.filter((value) => value.id === facetValueId);

                    if (facetValue.length > 0) {
                        break;
                    }
                }
            }

            return facetValue.length && this.isValueSelected(this.facet.id, facetValue[0].name);
        },

        getSubCategoryValue(parentCategory, subCategory) {
            return {
                id: subCategory.id,
                name: parentCategory.name + '_' + subCategory.name
            };
        }
    }
});
