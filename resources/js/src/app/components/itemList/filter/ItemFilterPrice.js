import Url from '../../../mixins/url'
import Vue from "vue";

Vue.component("item-filter-price", {

    delimiters: ["${", "}"],
    mixins: [Url],

    props: {
        template: {
            type: String,
            default: null
        },
        facet: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            priceMin: "",
            priceMax: "",
            // eslint-disable-next-line no-undef
            currency: App.activeCurrency
        };
    },

    computed: {
        isDisabled() {
            return (this.priceMin === "" && this.priceMax === "") ||
              (parseFloat(this.priceMin) > parseFloat(this.priceMax)) ||
              this.isLoading;
        },

        // eslint-disable-next-line no-undef
        ...Vuex.mapState({
            isLoading: state => state.itemList.isLoading
        })
    },

    created() {
        this.$options.template = this.template || "#vue-item-filter-price";

        const values = this.getSelectedFilterValue(this.facet.id);

        this.priceMin = values ? values.min : "";
        this.priceMax = values ? values.max : "";
    },

    methods: {
        selectAll(event) {
            event.target.select();
        },

        triggerFilter() {
            if (!this.isDisabled) {
                const facetValue = {
                    min: this.priceMin,
                    max: this.priceMax ? this.priceMax : Number.MAX_SAFE_INTEGER
                };

                this.updateSelectedFilters(this.facet.id, facetValue);
            }
        }
    }
});
