import Url from '../../../mixins/url'
import Vue from "vue";

Vue.component("findologic-item-filter", {

    delimiters: ["${", "}"],
    mixins: [Url],

    props: {
        template: {
            type: String,
            default: null
        },
        facet: {
            type: Object,
            required: true
        }
    },

    computed: {
        facets() {
            // eslint-disable-next-line vue/no-side-effects-in-computed-properties
            return this.facet.values.sort((facetA, facetB) => {
                if (facetA.position > facetB.position) {
                    return 1;
                } else if (facetA.position < facetB.position) {
                    return -1;
                } else {
                    return 0;
                }
            });
        },

        // eslint-disable-next-line no-undef
        ...Vuex.mapState({
            selectedFacets: state => state.itemList.selectedFacets,
            isLoading: state => state.itemList.isLoading
        })
    },

    created() {
        this.$options.template = this.template || "#vue-item-filter";
    },

    methods: {
        updateFacet(facetValue) {
            this.updateSelectedFilters(this.facet.id, facetValue.name);
        },

        getSubCategoryValue(parentCategory, subCategory) {
            return {
                id: subCategory.id,
                name: parentCategory.name + '_' + subCategory.name
            };
        }
    }
});
