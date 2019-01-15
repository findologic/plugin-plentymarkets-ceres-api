import url from './mixins/url'

Vue.component("item-filter", {
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
        this.$options.template = this.template || "#vue-item-filter";
    },

    methods:
        {
            updateFacet(facetValue)
            {
                console.log(this.facet);
                this.updateSelectedFilters(this.facet.id, facetValue.name);
            },

            isSelected(facetValue)
            {
                return this.isValueSelected(this.facet.id, facetValue.name);
            }
        }
});
