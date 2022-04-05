export default {
    props: [
        "template",
        "facet"
    ],

    data() {
        return {
            isOpen: false
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

        close: function () {
            this.isOpen = false;
        },

        toggle: function () {
            this.isOpen = !this.isOpen;
        }
    }
};
