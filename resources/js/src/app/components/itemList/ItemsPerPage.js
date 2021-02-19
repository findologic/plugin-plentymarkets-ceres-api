import Url from "../../mixins/url";
import Constants from '../../constants';
import Vue from "vue";

Vue.component("items-per-page", {
    mixins: [Url],

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
            this.setUrlParamValues([
                {
                    key: Constants.PARAMETER_ITEMS,
                    value: this.selectedValue
                },
                {
                    key: Constants.PARAMETER_PAGE,
                    value: 1
                }
            ]);
        },

        setSelectedValueByUrl() {
            const urlParams = this.getUrlParams(document.location.search);
            // eslint-disable-next-line no-undef
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
