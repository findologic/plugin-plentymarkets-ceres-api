import url from './mixins/url'

Vue.component("findologic-item-filter-price", {
    mixins: [url],

    delimiters: ["${", "}"],

    props: [
        "template",
        "facet"
    ],

    data()
    {
        return {
            priceMin: "",
            priceMax: "",
            currency: App.activeCurrency
        };
    },

    created()
    {
        this.$options.template = this.template || "#vue-findologic-item-filter-price";

        const values = this.getSearchParamValue(this.facet.id);

        this.priceMin = values ? values.min : "";
        this.priceMax = values ? values.max : "";
    },

    computed:
        {
            isDisabled()
            {
                return (this.priceMin === "" && this.priceMax === "") ||
                    (parseInt(this.priceMin) >= parseInt(this.priceMax)) ||
                    this.isLoading;
            },

            ...Vuex.mapState({
                isLoading: state => state.itemList.isLoading
            })
        },

    methods:
        {
            selectAll(event)
            {
                event.target.select();
            },

            triggerFilter()
            {
                if (!this.isDisabled)
                {
                    this.updateSelectedFilters(this.facet.id, {min: this.priceMin, max: this.priceMax});
                }
            }
        }
});
