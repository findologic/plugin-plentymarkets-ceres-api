import url from '../../../mixins/url'

Vue.component("findologic-item-filter", {
    mixins: [url],

    delimiters: ["${", "}"],

    props: [
        "template",
        "facet"
    ],

    data() {
        return {
            facetType: null
        }
    },

    computed: {
        facets() {
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

        isSelected() {
            return typeof this.getSelectedFilters().find(element => element.id == this.facet.id) !== 'undefined';
        },

        ...Vuex.mapState({
            selectedFacets: state => state.itemList.selectedFacets,
            isLoading: state => state.itemList.isLoading
        }),

        selectedValuesCount() {
            let count = 0;

            this.facet.values.forEach(function(value) {
                if (value.selected) {
                    count++;
                }
            });

            return count;
        }
    },

    created() {
        this.$options.template = this.template || "#vue-item-filter";
        this.facetType = (typeof this.facet.findologicFilterType !== 'undefined') ? this.facet.findologicFilterType : this.facet.type;
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
        },
    }
});
