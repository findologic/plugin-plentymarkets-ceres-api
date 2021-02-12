import url from '../../../mixins/url'
import baseDropdown from "../../../mixins/baseDropdown";

Vue.component("item-category-dropdown", {
    mixins: [url, baseDropdown],

    computed: {
        dropdownLabel() {
            let selectedFilters = this.getSelectedFilters();
            let label = null;

            for (let i = 0; i < selectedFilters.length; i++) {
                let facet = selectedFilters[i];

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
