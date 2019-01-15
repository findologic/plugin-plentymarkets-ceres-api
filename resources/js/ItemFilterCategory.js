import url from './mixins/url'

Vue.component("item-filter-category", {
    mixins: [url],

    delimiters: ["${", "}"],

    props: [
        "template",
        "facet"
    ],

    computed:
    {
        ...Vuex.mapState({
            isLoading: state => state.itemList.isLoading
        })
    },

    created()
    {
        this.$options.template = this.template || "#vue-item-filter-category";
    },

    methods:
        {
            updateFacet(facetValue)
            {
                console.log('ItemFilterCategory');
                this.updateSelectedFilters(this.facet.id, facetValue.name);
            },

            isSelected(facetValue)
            {
                return this.isValueSelected(this.facet.id, facetValue);
            },

            getSubCategoryValue(parentCategory, subCategory)
            {
                return parentCategory.name + '_' + subCategory.name;
            }
        }
});
