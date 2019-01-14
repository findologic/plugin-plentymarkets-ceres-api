Vue.component("findologic-item-filter-tag-list", {

    delimiters: ["${", "}"],

    props: [
        "template"
    ],

    computed: Vuex.mapState({
        tagList: state => state.itemList.selectedFacets
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
