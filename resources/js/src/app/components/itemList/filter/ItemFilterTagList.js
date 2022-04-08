import url from "../../../mixins/url";

Vue.component("item-filter-tag-list", {
    mixins: [url],

    delimiters: ["${", "}"],

    props:
    {
        template:
            {
                type: String,
                default: "#vue-item-filter-tag-list"
            },
        marginClasses:
            {
                type: String,
                default: null
            },
        marginInlineStyles:
            {
                type: String,
                default: null
            }
    },

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
