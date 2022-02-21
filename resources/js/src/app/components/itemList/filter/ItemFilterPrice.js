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
                (parseFloat(this.priceMin) > parseFloat(this.priceMax)) ||
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
                    min: this.priceMin ? this.priceMin : 0,
                    max: this.priceMax ? this.priceMax : this.getMaxPrice()
                };

                this.updateSelectedFilters(this.facet.id, facetValue);
            }
        },

        getMaxPrice() {
            let maxPrice = this.facet.values[this.facet.values.length -1].name.split(' - ')[1];

            return maxPrice ? maxPrice : Number.MAX_SAFE_INTEGER;
        }
    }
});
