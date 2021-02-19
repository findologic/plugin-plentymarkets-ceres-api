import Url from '../../../mixins/url'
import Vue from "vue";

Vue.component("item-color-tiles", {
    mixins: [Url],

    props: [
        "template",
        "facet"
    ],

    created() {
        this.$options.template = this.template || "#vue-item-color-tiles";
    },

    computed: {
        // eslint-disable-next-line no-undef
        ...Vuex.mapState({
            isLoading: state => state.itemList.isLoading,
        })
    },

    methods: {
        isSelected(facetValueName) {
            const facetValue = this.facet.values.filter((value) => value.name === facetValueName);

            return facetValue.length && this.isValueSelected(this.facet.id, facetValue[0].name);
        },

        tileClicked: function (value) {
            this.updateSelectedFilters(this.facet.id, value);
        }
    }
});
