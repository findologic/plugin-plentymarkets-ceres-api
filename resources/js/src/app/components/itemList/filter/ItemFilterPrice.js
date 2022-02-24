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

        this.MIN_PRICE = 0;
        this.MAX_PRICE = Number.MAX_SAFE_INTEGER;

        const values = this.getSelectedFilterValue(this.facet.id);

        this.priceMin = values ? values.min : "";
        this.priceMax = values ? values.max : "";

    },

    computed: {
        isDisabled() {
            return (this.priceMin === "" && this.priceMax === "") ||
                (parseFloat(this.priceMin) > parseFloat(this.priceMax)) ||
                isNaN(this.priceMin) ||
                isNaN(this.priceMax) ||
                this.priceMin === '' ||
                this.priceMax === '' ||
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
                    min: this.priceMin ? this.priceMin : this.MIN_PRICE,
                    max: this.priceMax ? this.priceMax : this.getMaxPrice()
                };

                this.updateSelectedFilters(this.facet.id, facetValue);
            }
        },

        getMaxPrice() {
            const maxPrice = this.facet.values[this.facet.values.length -1].name.split(' - ')[1];

            return maxPrice ? maxPrice : this.MAX_PRICE;
        }
    }
});
