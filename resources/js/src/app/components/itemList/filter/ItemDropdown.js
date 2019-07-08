import url from '../../../mixins/url'

Vue.component("item-dropdown", {
    mixins: [url],

    props: [
        "template",
        "facet"
    ],

    data: function () {
        return {
            filterDropdown: this.getSelectedValue()
        }
    },

    created() {
        this.$options.template = this.template || "#vue-item-dropdown";
    },

    computed: {
        ...Vuex.mapState({
            isLoading: state => state.itemList.isLoading
        })
    },

    methods: {
        getSelectedValue() {
            let self = this;

            let facetValue = this.facet.values.filter((value) => self.isValueSelected(self.facet.id, value.name));

            if (facetValue.length === 0) {
                return ''
            }

            return facetValue[0].name;
        },

        selected: function (value) {
            this.updateSelectedFilters(this.facet.id, value);
        }
    }
});
