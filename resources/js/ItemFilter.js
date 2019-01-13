import url from './mixins/url'

Vue.component("findologic-item-filter", {
    mixins: [url],

    delimiters: ["${", "}"],

    props: [
        "template",
        "facet"
    ],

    computed:
        {
            facets()
            {
                return this.facet.values.sort((facetA, facetB) =>
                {
                    if (facetA.position > facetB.position)
                    {
                        return 1;
                    }
                    if (facetA.position < facetB.position)
                    {
                        return -1;
                    }

                    return 0;
                });
            },

            ...Vuex.mapState({
                selectedFacets: state => state.itemList.selectedFacets,
                isLoading: state => state.itemList.isLoading
            })
        },

    created()
    {
        this.$options.template = this.template || "#vue-findologic-item-filter";
    },

    methods:
        {
            updateFacet(facetValue)
            {
                this.updateSelectedFilters(this.facet.id, facetValue.name);
            },

            isSelected(facetValue)
            {
                return this.isValueSelected(this.facet.id, facetValue.name);
            }
        }
});
