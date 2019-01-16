import url from "./mixins/url";

Vue.component("items-per-page", {
    mixins: [url],

    delimiters: ["${", "}"],

    props: [
        "paginationValues",
        "template"
    ],

    data() {
        return {
            selectedValue: null
        };
    },

    created() {
        this.$options.template = this.template || "#vue-items-per-page";
        this.setSelectedValueByUrl();
    },

    methods: {
        itemsPerPageChanged() {
            this.setUrlParamValue('items', this.selectedValue);
        },

        setSelectedValueByUrl() {
            const urlParams = this.getUrlParams(document.location.search);
            const defaultItemsPerPage = App.config.pagination.columnsPerPage * App.config.pagination.rowsPerPage[0];

            if (urlParams.items) {
                if (this.paginationValues.includes(parseInt(urlParams.items))) {
                    this.selectedValue = urlParams.items;
                } else {
                    this.selectedValue = defaultItemsPerPage;
                }
            } else {
                this.selectedValue = defaultItemsPerPage;
            }

            this.$store.commit("setItemsPerPage", parseInt(this.selectedValue));
        }
    }
});
