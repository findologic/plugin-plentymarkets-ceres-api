export default {
    props: [
        "template",
        "facet"
    ],

    data: function () {
        return {
            isOpen: false
        }
    },

    created() {
        this.$options.template = this.template || "#vue-item-dropdown";
    },

    computed: {
        // eslint-disable-next-line no-undef
        ...Vuex.mapState({
            isLoading: state => state.itemList.isLoading
        })
    },

    methods: {
        selected: function (value) {
            this.updateSelectedFilters(this.facet.id, value);
        },

        close: function () {
            this.isOpen = false;
        },

        toggle: function () {
            this.isOpen = !this.isOpen;
        }
    }
};
