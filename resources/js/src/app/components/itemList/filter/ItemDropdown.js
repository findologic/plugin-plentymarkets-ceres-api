import url from '../../../mixins/url'

Vue.component("item-dropdown", {
    mixins: [url],

    props: [
        "template",
        "facet"
    ],

    data: function () {
        return {
            isShowDropdown: false,
            selectedValue: null
        }
    },

    created() {
        this.$options.template = this.template || "#vue-item-dropdown";

        this.selectedValue = this.getSelectedValue();
    },

    computed: {
        ...Vuex.mapState({
            isLoading: state => state.itemList.isLoading
        })
    },

    methods: {
        getSelectedValue: function () {
            let selected = this.getSelectedFilterValue(this.facet.id);

            if (selected != null) {
                return selected[0];
            }
        },

        selected: function (value) {
            this.updateSelectedFilters(this.facet.id, value);
        },

        hideDropdown: function () {
            this.isShowDropdown = false;
        },

        toggleDropdown: function () {
            this.isShowDropdown = !this.isShowDropdown;
        }
    }
});
