import url from '../../../mixins/url'

Vue.component("item-price-slider", {
    mixins: [url],

    props: [
        "template",
        "facet"
    ],

    data() {
        return {
            priceFrom: "",
            priceTo: ""
        };
    },

    created() {
        let self = this;

        this.$options.template = this.template || "#vue-item-price-slider";

        const values = this.getSelectedFilterValue(this.facet.id);

        this.priceFrom = values ? values.min : this.facet.minPrice;
        this.priceTo = values ? values.max : this.facet.maxPrice;

        $(function() {
            $("#price-slider-range").slider({
                step: self.facet.step,
                range: true,
                min: self.facet.minPrice,
                max: self.facet.maxPrice,
                values: [ self.priceFrom, self.priceTo ],
                slide: function( event, ui ) {
                    self.priceFrom = ui.values[0];
                    self.priceTo = ui.values[1];
                }
            });
        });
    },

    computed: {
        isDisabled() {
            return (this.priceFrom === "" && this.priceTo === "") ||
                (parseInt(this.priceFrom) >= parseInt(this.priceTo)) ||
                this.isLoading;
        },

        ...Vuex.mapState({
            isLoading: state => state.itemList.isLoading
        })
    },

    methods: {
        triggerFilter() {
            if (!this.isDisabled) {
                let facetValue = {
                    min: this.priceFrom,
                    max: this.priceTo ? this.priceTo : Number.MAX_SAFE_INTEGER
                };

                this.updateSelectedFilters(this.facet.id, facetValue);
            }
        }
    }
});
