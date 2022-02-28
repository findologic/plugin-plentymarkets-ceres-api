import url from '../../../mixins/url'
import baseDropdown from "../../../mixins/baseDropdown";

Vue.component("item-category-dropdown", {
    mixins: [url, baseDropdown],

    computed: {
        dropdownLabel() {
            let selectedFilters = this.getSelectedFilters();

            if (selectedFilters.length === 0 && this.facet.id === 'cat' && this.facet.values.length === 1) {
                return this.facet.values[0].name;
            }

            let label = null;

            for (let i = 0; i < selectedFilters.length; i++) {
                let facet = selectedFilters[i];

                if (facet.id === this.facet.id) {
                    label = facet.name;
                    break;
                }
            }

            return label;
        },

        isSelected() {
            if (this.facet.id === 'cat' && this.facet.values.length === 1) {
                return true;
            }

            return typeof this.getSelectedFilters().find(element => element.id == this.facet.id) !== 'undefined';
        },
    },

    methods: {
        getSubCategoryName(parentCategory, subCategory) {
            return parentCategory.name + '_' + subCategory.name;
        },

        categorySelected(category) {
            return this.isCategorySelected(category);
        }
    }
});
