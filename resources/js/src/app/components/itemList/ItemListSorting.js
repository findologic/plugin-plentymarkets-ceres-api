import url from "../../mixins/url";
import Constants from '../../constants';

Vue.component("item-list-sorting", {
    mixins: [url],

    delimiters: ["${", "}"],

    props: [
        "sortingList",
        "defaultSorting",
        "template"
    ],

    data() {
        return {
            selectedSorting: {}
        };
    },

    created() {
        this.$options.template = this.template || "#vue-item-list-sorting";
        this.setSelectedValue();
    },

    methods: {
        updateSorting() {
            this.setUrlParamValues([
                {
                    key: Constants.PARAMETER_SORTING,
                    value: this.selectedSorting
                },
                {
                    key: Constants.PARAMETER_PAGE,
                    value: 1
                }
            ]);
        },

        /**
         * Determine the initial value and set it in the vuex storage.
         */
        setSelectedValue() {
            const urlParams = this.getUrlParams(document.location.search);

            if (urlParams.sorting) {
                this.selectedSorting = urlParams.sorting;
            } else {
                this.selectedSorting = this.defaultSorting;
            }

            this.$store.commit("setItemListSorting", this.selectedSorting);
        }
    }
});
