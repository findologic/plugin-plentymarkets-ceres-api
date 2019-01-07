import UrlService from "services/UrlService";

Vue.component("findologic-item-filter-price", {

    delimiters: ["${", "}"],

    props:
        {
            template:
                {
                    type: String,
                    default: "#vue-item-filter-price"
                }
        },

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
        console.log('findologic item filter price');
        this.$options.template = this.template || "#vue-findologic-item-filter-price";

        const urlParams = UrlService.getUrlParams(document.location.search);

        this.priceMin = urlParams.priceMin || "";
        this.priceMax = urlParams.priceMax || "";
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
                    this.$store.dispatch("selectPriceFacet", {priceMin: this.priceMin, priceMax: this.priceMax});
                }
            }
        }
});
