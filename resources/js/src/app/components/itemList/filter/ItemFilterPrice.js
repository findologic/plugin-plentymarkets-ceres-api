import url from '../../../mixins/url'

Vue.component("item-filter-price", {
    mixins: [url],

    delimiters: ["${", "}"],

    props: [
        "template",
        "facet"
    ],

    data() {
        return {
            priceMin: "",
            priceMax: "",
            currency: App.activeCurrency
        };
    },

    created() {
        this.$options.template = this.template || "#vue-item-filter-price";

        const values = this.getSelectedFilterValue(this.facet.id);

        this.priceMin = values ? values.min : "";
        this.priceMax = values ? values.max : "";
    },

    computed: {
        isDisabled() {
            return (this.priceMin === "" && this.priceMax === "") ||
                (parseInt(this.priceMin) >= parseInt(this.priceMax)) ||
                this.isLoading;
        },

        ...Vuex.mapState({
            isLoading: state => state.itemList.isLoading
        })
    },

    methods: {
        selectAll(event) {
            event.target.select();
        },

        triggerFilter() {
            if (!this.isDisabled) {
                let facetValue = {
                    min: this.priceMin,
                    max: this.priceMax ? this.priceMax : Number.MAX_SAFE_INTEGER
                };

                this.updateSelectedFilters(this.facet.id, facetValue);
            }
        }
    }
});
