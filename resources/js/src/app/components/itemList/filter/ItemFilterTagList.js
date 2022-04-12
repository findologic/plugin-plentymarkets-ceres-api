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
        },
        facetData:
        {
            type: Array,
            default()
            {
                return [];
            }
        },
    },

    computed: {
        tagList() {
            return this.getSelectedFilters();
        }
    },

    created() {
        this.$options.template = this.template || "#vue-item-filter-tag-list";

        const facets = this.$store.state.itemList.facets;
        if (!facets && !facets.length)
        {
            this.$store.commit("addFacets", this.facetData);
        }
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
