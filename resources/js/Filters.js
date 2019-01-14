import url from './mixins/url'

Vue.component("findologic-filter-list", {
    mixins: [url],

    delimiters: ["${", "}"],

    props: [
        "template",
        "facetData"
    ],

    data() {
        return {
            isActive: false
        };
    },

    computed: Vuex.mapState({
        facets(state) {
            return state.itemList.facets.sort((facetA, facetB) => {
                if (facetA.position > facetB.position) {
                    return 1;
                }
                if (facetA.position < facetB.position) {
                    return -1;
                }

                return 0;
            });
        }
    }),

    created() {
        this.$store.commit("setFacets", this.facetData);

        this.$options.template = this.template || "#vue-findologic-filter-list";

        const urlParams = this.getUrlParams(document.location.search);

        let selectedFacets = [];

        if (urlParams.facets) {
            selectedFacets = urlParams.facets.split(",");
        }

        if (urlParams.priceMin || urlParams.priceMax) {
            const priceMin = urlParams.priceMin || "";
            const priceMax = urlParams.priceMax || "";

            this.$store.commit("setPriceFacet", {priceMin: priceMin, priceMax: priceMax});

            selectedFacets.push("price");
        }

        if (selectedFacets.length > 0) {
            this.$store.commit("setSelectedFacetsByIds", selectedFacets);
        }
    },

    methods:
        {
            toggleOpeningState() {
                window.setTimeout(() => {
                    this.isActive = !this.isActive;
                }, 300);
            }
        }

});
