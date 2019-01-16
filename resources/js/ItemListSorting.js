import url from "./mixins/url";

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
                this.setUrlParamValue('sorting', this.selectedSorting);
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
