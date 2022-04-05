import url from '../../../mixins/url'

Vue.component("item-filter-image", {
    mixins: [url],

    props: [
        "template",
        "facet",
        "fallbackImage"
    ],

    created() {
        this.$options.template = this.template || "#findologic-item-filter-image";
    },

    computed: {
        ...Vuex.mapState({
            isLoading: state => state.itemList.isLoading,
        })
    },

    methods: {
        updateFacet(facetValue) {
            this.updateSelectedFilters(this.facet.id, facetValue.name);
        },

        handleImageError(event) {
            event.target.src = this.fallbackImage;
        }
    }
});
