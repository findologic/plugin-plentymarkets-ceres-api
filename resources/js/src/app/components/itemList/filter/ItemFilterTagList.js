import Url from '../../../mixins/url';
import Vue from 'vue';

Vue.component('item-filter-tag-list', {

    delimiters: ['${', '}'],
    mixins: [Url],

    props:
    {
        template:
            {
                type: String,
                default: '#vue-item-filter-tag-list'
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
        },
        facetNames() {
            return this.getFacetIdNameMap();
        }
    },

    created() {
        this.$options.template = this.template || '#vue-item-filter-tag-list';
    },

    methods: {
        removeTag(tag) {
            this.removeSelectedFilter(tag.id, tag.name);
        },

        resetAllTags() {
            this.removeAllAttribsAndRefresh();
        },

        getFacetIdNameMap() {
            const map = {};

            this.$store.state.itemList.facets.forEach(facet => {
                map[facet.id] = facet.name;
            });

            return map;
        }
    }
});
