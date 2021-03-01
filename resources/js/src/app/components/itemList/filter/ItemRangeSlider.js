import Url from '../../../mixins/url'
import Vue from "vue";

Vue.component("item-range-slider", {
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
            valueFrom: "",
            valueTo: ""
        };
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

        // eslint-disable-next-line no-undef
        ...Vuex.mapState({
            isLoading: state => state.itemList.isLoading
        })
    },

    created() {
        // eslint-disable-next-line @typescript-eslint/no-this-alias
        const self = this;

        this.$options.template = this.template || "#vue-item-range-slider";

        const values = this.getSelectedFilterValue(this.facet.id);

        this.valueFrom = values ? values.min : this.facet.minValue;
        this.valueTo = values ? values.max : this.facet.maxValue;

        // eslint-disable-next-line no-undef
        $(document).ready(function () {
            const element = self.$el.querySelector('#' + self.sanitizedFacetId);

            const slider = window.noUiSlider.create(element, {
                step: self.facet.step,
                start: [self.valueFrom, self.valueTo],
                connect: true,
                range: {
                    'min': self.facet.minValue,
                    'max': self.facet.maxValue
                }
            });

            slider.on('update', function (ui) {
                self.valueFrom = ui[0];
                self.valueTo = ui[1];
            });
        });
    },

    methods: {
        triggerFilter() {
            if (!this.isDisabled) {
                const facetValue = {
                    min: this.valueFrom,
                    max: this.valueTo ? this.valueTo : Number.MAX_SAFE_INTEGER
                };

                this.updateSelectedFilters(this.facet.id, facetValue);
            }
        }
    }
});
