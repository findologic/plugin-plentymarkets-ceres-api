import url from '../../../mixins/url'

Vue.component("item-dropdown", {
    mixins: [url],

    props: [
        "template",
        "facet"
    ],

    data: function () {
        return {
            isShowDropdown: false
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
