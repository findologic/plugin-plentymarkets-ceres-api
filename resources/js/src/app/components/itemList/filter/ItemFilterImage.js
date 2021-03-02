import url from '../../../mixins/url'

Vue.component("item-filter-image", {
    mixins: [url],

    props: [
        "template",
        "facet",
        "fallbackImage"
    ],

    created() {
        this.$options.template = this.template || "#vue-findologic-item-filter-image";
    },

    computed: {
        ...Vuex.mapState({
            isLoading: state => state.itemList.isLoading,
        })
    },

    methods: {
        handleImageError(event) {
            event.target.src = this.fallbackImage;
        }
    }
});
