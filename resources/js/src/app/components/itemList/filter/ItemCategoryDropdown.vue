<template>
  <!-- SSR:template(findologic-item-category-dropdown) -->
  <div class="fl-dropdown">
    <div
      class="fl-dropdown-container fl-category-dropdown-container custom-select"
      tabindex="0"
      @click="toggle()"
      @blur="close()"
    >
      <span
        v-if="dropdownLabel"
        class="fl-dropdown-label"
        v-text="dropdownLabel"
      />
      <span
        v-else
        class="fl-dropdown-label"
      >{{ TranslationService.translate("Findologic::Template.pleaseSelect") }}</span>
      <ul
        v-show="isOpen"
        class="fl-dropdown-content form-check"
      >
        <li
          v-for="value in getCategories"
          :key="value.id"
          class="fl-dropdown-item"
          :class="{'form-check-label': !isSelected}"
          rel="nofollow"
          @click.stop="close(); selected(getParentCategoryName(value));"
        >
          <input
            :id="'option-' + value.id"
            class="form-check-input hidden-xs-up"
            type="checkbox"
            :checked="isSelected"
            :disabled="isLoading"
          >
          <label
            :for="'option-' + value.id"
            :class="{'form-check-label': isSelected}"
            rel="nofollow"
            v-text="value.name"
          />
          <div
            v-if="!isCategorySelected(value) && value.count"
            class="filter-badge"
            v-text="value.count"
          />
          <ul
            v-if="isSelected && value.items.length > 0 && !isInCategoryPage"
            class="form-check subcategories"
          >
            <li
              v-for="subcategory in value.items"
              :key="subcategory.id"
              class="fl-dropdown-item"
              :class="{'form-check-label': !isCategorySelected(subcategory)}"
              @click.stop="close(); selected(getSubCategoryName(value, subcategory));"
            >
              <input
                :id="'option-' + subcategory.id"
                class="form-check-input hidden-xs-up"
                type="checkbox"
                :checked="isCategorySelected(subcategory)"
                :disabled="isLoading"
              >
              <label
                :for="'option-' + subcategory.id"
                :class="{'form-check-label': isCategorySelected(subcategory)}"
                rel="nofollow"
                v-text="subcategory.name"
              />
              <div
                v-if="!isCategorySelected(subcategory) && subcategory.count"
                class="filter-badge"
                v-text="subcategory.count"
              />
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
  <!-- /SSR -->
</template>

<script lang="ts">
import BaseDropdown from '../../../mixins/baseDropdown';
import { CategoryFacet, FacetAware, FacetValue, PlentyVuexStore, TemplateOverridable } from '../../../shared/interfaces';
import { defineComponent, onMounted, ref } from '@vue/composition-api';
import UrlBuilder from '../../../shared/UrlBuilder';
import TranslationService from '../../../shared/TranslationService';

interface CategoryDropdownProps extends TemplateOverridable, FacetAware {
  currentCategory: CategoryFacet[];
}

export default defineComponent({
  mixins: [
    BaseDropdown
  ],

  setup(props: CategoryDropdownProps, { root }) {
    root.$options.template = props.template || '#vue-item-dropdown';

    const buildDropdownLabel = () => {
      const selectedFilters = UrlBuilder.getSelectedFilters(root.$store as PlentyVuexStore);

      for (let i = 0; i < selectedFilters.length; i++) {
        const facet = selectedFilters[i];

        if (facet.id === props.facet.id) {
          return facet.name;
        }
      }

      return '';
    };

    const isSelected = (): boolean => {
      if (typeof props.currentCategory !== 'undefined' && isParentCategorySelected()) {
        return false;
      }

      return typeof UrlBuilder.getSelectedFilters().find(element => element.id === props.facet.id) !== 'undefined';
    };

    const isInCategoryPage = (): boolean => {
      return typeof props.currentCategory !== 'undefined';
    };

    const getCategories = (): FacetValue[] | undefined  => {
      if (
          typeof props.currentCategory !== 'undefined' &&
          props.facet.values?.[0].name === props.currentCategory[0].name
      ) {
        console.log('With currentCategory');
        console.log(props.facet.values?.[0].items);
        return props.facet.values?.[0].items;
      }

      console.log('Without currentCategory');
      console.log(props.facet.values);

      return props.facet.values;
    };

    const getSubCategoryName = (parentCategory: FacetValue, subCategory: FacetValue): string => {
      return getParentCategoryName(parentCategory) + '_' + subCategory.name;
    };

    const getParentCategoryName = (category: FacetValue): string | undefined => {
      if (typeof props.currentCategory === 'undefined' || props.currentCategory[0].name === category.name) {
        return category.name;
      }
    };

    const isParentCategorySelected = (): boolean => {
      return typeof UrlBuilder.getSelectedFilters().find(element =>
          (element.id === props.facet.id && element.name === props.currentCategory[0].name)) !== 'undefined';
    };

    const isCategorySelected = (category: FacetValue): boolean => {
      const selectedFilters = UrlBuilder.getSelectedFilters();
      let splittedSelectedCategories = [] as Array<string> | undefined;

      for (let i = 0; i < selectedFilters.length; i++) {
        if (selectedFilters[i].id !== props.facet.id) {
          continue;
        }

        splittedSelectedCategories = selectedFilters?.[i].name?.split('>');

        break;
      }

      return typeof splittedSelectedCategories?.find(
          categoryName => categoryName.trim() === category.name) !== 'undefined';
    };

    const dropdownLabel = ref('');

    onMounted(() => {
      dropdownLabel.value = buildDropdownLabel() as string;
    });

    return {
      dropdownLabel,
      isSelected,
      isInCategoryPage,
      getCategories,
      getSubCategoryName,
      getParentCategoryName,
      isCategorySelected,
      TranslationService
    };
  }
});
</script>
