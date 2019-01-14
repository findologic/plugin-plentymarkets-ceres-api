import url from "./mixins/url";

Vue.component("item-filter-tag-list", {
    mixins: [url],

    delimiters: ["${", "}"],

    props: [
        "template"
    ],

    computed: Vuex.mapState({
        tagList: state => this.getSelectedFilters()
    }),

    created()
    {
        this.$options.template = this.template || "#vue-findologic-item-filter-tag-list";
    },

    methods:
        {
            removeTag(tag)
            {
                this.$store.dispatch("selectFacet", tag);
            }
        }
});
