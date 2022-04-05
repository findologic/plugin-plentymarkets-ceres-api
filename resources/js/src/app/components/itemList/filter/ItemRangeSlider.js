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

        this.$options.template = this.template || "#findologic-item-range-slider";

        const values = this.getSelectedFilterValue(this.facet.id);

        this.valueFrom = values ? values.min : this.facet.minValue;
        this.valueTo = values ? values.max : this.facet.maxValue;

        $(document).ready(function () {
            var element = self.$el.querySelector('#' + self.sanitizedFacetId);

            var slider = window.noUiSlider.create(element, {
                step: self.facet.step,
                start: [self.valueFrom, self.valueTo],
                connect: true,
                range: {
                    'min': Math.min(self.valueFrom, self.facet.minValue),
                    'max': Math.max(self.valueTo, self.facet.maxValue)
                }
            });

            slider.on('update', function (ui) {
                self.valueFrom = ui[0];
                self.valueTo = ui[1];
            });
        });
    },

    computed: {
        sanitizedFacetId() {
            return 'fl-range-slider-' + this.facet.id.replace(/\W/g, '-').replace(/-+/, '-').replace(/-$/, '');
        },

        isDisabled() {
            return (this.valueFrom === '' && this.valueTo === '') ||
                (parseFloat(this.valueFrom) > parseFloat(this.valueTo)) ||
                isNaN(this.valueFrom) ||
                isNaN(this.valueTo) ||
                this.valueFrom === '' ||
                this.valueTo === '' ||
                this.isLoading;
        },

        ...Vuex.mapState({
            isLoading: state => state.itemList.isLoading
        }),
    },

    watch: {
        valueFrom(value) {
            this.valueFrom = this.fixDecimalSeparator(value);
            this.setCustomValidationMessage();
        },

        valueTo(value) {
            this.valueTo =  this.fixDecimalSeparator(value);
            this.setCustomValidationMessage();
        }
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
        },

        fixDecimalSeparator(value) {
            if (typeof value === 'number') {
                value = value.toString();
            }

            if (value.includes('.')) {
                value = value.replace(',', '');
            } else {
                value = value.replace(',', '.');
            }

            return value;
        },

        setCustomValidationMessage() {
            this.$el.querySelectorAll('input.fl-range-input[data-custom-validation-message]').forEach((input) => {
                // Must be reset before the validity check as existence of custom validity counts as a validation error.
                input.setCustomValidity('');

                if (!input.checkValidity()) {
                    input.setCustomValidity(input.dataset.customValidationMessage);
                }
            });
        }
    }
});
