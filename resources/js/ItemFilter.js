Vue.component("findologic-item-filter", {

    delimiters: ["${", "}"],

    props: [
        "template",
        "facet"
    ],

    computed:
        mapState({
            selectedFacets: state => state.itemList.selectedFacets,
            isLoading: state => state.itemList.isLoading,
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
            }
        }),

    created()
    {
        console.log("findologic item filter");
        this.$options.template = this.template || "#vue-findologic-item-filter";
    },

    methods:
        {
            updateFacet(facetValue)
            {
                this.$store.dispatch("selectFacet", facetValue);
            },

            isSelected(facetValueId)
            {
                return this.selectedFacets.findIndex(selectedFacet => selectedFacet.id === facetValueId) > -1;
            }
        }
});
