import Url from '../../../mixins/url'
import baseDropdown from "../../../mixins/baseDropdown";
import Vue from 'vue';

Vue.component("item-category-dropdown", {
    mixins: [Url, baseDropdown],

    computed: {
        dropdownLabel() {
            const selectedFilters = this.getSelectedFilters();
            let label = null;

            for (let i = 0; i < selectedFilters.length; i++) {
                const facet = selectedFilters[i];

                if (facet.id === this.facet.id) {
                    label = facet.name;
                    break;
                }
            }

            return label;
        }
    },

    methods: {
        getSubCategoryName(parentCategory, subCategory) {
            return parentCategory.name + '_' + subCategory.name;
        }
    }
});
