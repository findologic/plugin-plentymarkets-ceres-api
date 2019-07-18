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

        // window.addEventListener('click', this.onClick);

        this.selectedValue = this.getSelectedValue();
    },

    beforeDestroy() {
        // window.removeEventListener('click', this.onClick);
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

        // onClick(event) {
        //     if (!this.$el.children[0].contains(event.target)) {
        //         this.hideDropdown();
        //     } else {
        //         this.showDropdown();
        //     }
        // },

        selected: function (value) {
            this.updateSelectedFilters(this.facet.id, value);
        },

        // showDropdown: function () {
        //     this.isShowDropdown = true;
        // },
        //
        hideDropdown: function () {
            this.isShowDropdown = false;
        },

        toggleDropdown: function () {
            this.isShowDropdown = !this.isShowDropdown;
        }
    }
});
