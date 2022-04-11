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
        },

        /**
         * @returns {boolean}
         */
        isSelected() {
            if (typeof this.currentCategory !== 'undefined' && this.isParentCategorySelected()) {
                return false;
            }

            return typeof this.getSelectedFilters().find(element => element.id === this.facet.id) !== 'undefined';
        },

        /**
         * @returns {DataTransferItemList}
         */
        getCategories() {
            if (
                typeof this.currentCategory !== 'undefined' &&
                this.facet.values[0].name === this.currentCategory[0].name
            ) {
                return this.facet.values[0].items;
            }

            return this.facet.values;
        },

        /**
         * If not in category page, then currentCategory property is undefined.
         * @returns {boolean}
         */
        isInCategoryPage() {
            return typeof this.currentCategory !== 'undefined';
        }
    },

    methods: {
        getSubCategoryName(parentCategory, subCategory) {
            return this.getParentCategoryName(parentCategory) + '_' + subCategory.name;
        },

        /**
         * @param {Object} category
         * @returns {string}
         */
        getParentCategoryName(category) {
            if (typeof this.currentCategory === 'undefined' || this.currentCategory[0].name === category.name) {
                return category.name;
            }

            return this.currentCategory[0].name + '_' + category.name;
        },

        /**
         * @returns {boolean}
         */
        isParentCategorySelected() {
            return typeof this.getSelectedFilters().find(element => (
                element.id === this.facet.id && element.name === this.currentCategory[0].name)) !== 'undefined'
        }
    }
});
