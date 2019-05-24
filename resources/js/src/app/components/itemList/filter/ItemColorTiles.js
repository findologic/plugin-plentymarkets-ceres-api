import url from '../../../mixins/url'

Vue.component("item-color-tiles", {
    mixins: [url],

    props: [
        "template",
        "facet"
    ],

    created() {
        this.$options.template = this.template || "#vue-item-color-tiles";
    },

    computed: {
        ...Vuex.mapState({
            isLoading: state => state.itemList.isLoading
        })
    },

    methods: {
        isSelected(facetValueName) {
            let facetValue = this.facet.values.filter((value) => value.name === facetValueName);

            return facetValue.length && this.isValueSelected(this.facet.id, facetValue[0].name);
        },

        tileClicked: function (value) {
            this.updateSelectedFilters(this.facet.id, value);
        }
    }
});
