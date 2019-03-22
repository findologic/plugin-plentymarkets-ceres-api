import url from "../../../mixins/url";

Vue.component("item-filter-tag-list", {
    mixins: [url],

    delimiters: ["${", "}"],

    props: [
        "template"
    ],

    computed: {
        tagList() {
            return this.getSelectedFilters();
        }
    },

    created() {
        this.$options.template = this.template || "#vue-item-filter-tag-list";
    },

    methods: {
        removeTag(tag) {
            this.removeSelectedFilter(tag.id, tag.name);
        },

        resetAllTags() {
            this.removeAllAttribsAndRefresh();
        }
    }
});
