import url from '../../../mixins/url'

Vue.component("item-range-slider", {
    mixins: [url],

    props: [
        "template",
        "facet"
    ],

    data() {
        return {
            valueFrom: "",
            valueTo: ""
        };
    },

    created() {
        let self = this;

        this.$options.template = this.template || "#vue-item-range-slider";

        var sliders = document.getElementById('slider');
        for (let slider of sliders) {
            window.noUiSlider.create(slider, {
                start: [20, 80],
                connect: true,
                range: {
                    'min': 0,
                    'max': 100
                }
            });
        }

        const values = this.getSelectedFilterValue(this.facet.id);

        this.valueFrom = values ? values.min : this.facet.minValue;
        this.valueTo = values ? values.max : this.facet.maxValue;

        $(function() {
            $("#" + self.sanitizedFacetId).slider({
                step: self.facet.step,
                range: true,
                min: self.facet.minValue,
                max: self.facet.maxValue,
                values: [ self.valueFrom, self.valueTo ],
                slide: function( event, ui ) {
                    self.valueFrom = ui.values[0];
                    self.valueTo = ui.values[1];
                }
            });
        });
    },

    computed: {
        sanitizedFacetId() {
            return 'fl-range-slider-' + this.facet.id.replace(/\W/g, '-').replace(/-+/, '-').replace(/-$/, '');
        },

        isDisabled() {
            return (this.valueFrom === "" && this.valueTo === "") ||
                (parseFloat(this.valueFrom) > parseFloat(this.valueTo)) ||
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
                    min: this.valueFrom,
                    max: this.valueTo ? this.valueTo : Number.MAX_SAFE_INTEGER
                };

                this.updateSelectedFilters(this.facet.id, facetValue);
            }
        }
    }
});
