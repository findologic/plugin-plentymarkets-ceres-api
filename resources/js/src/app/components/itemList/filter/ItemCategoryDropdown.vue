<template>
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
          v-for="value in facet.values"
          :key="value.id"
          class="fl-dropdown-item"
          :class="{'form-check-label': !value.selected}"
          rel="nofollow"
          @click.stop="close(); selected(value.name);"
        >
          <input
            :id="'option-' + value.id"
            class="form-check-input hidden-xs-up"
            type="checkbox"
            :checked="value.selected"
            :disabled="isLoading"
          >
          <label
            :for="'option-' + value.id"
            :class="{'form-check-label': value.selected}"
            rel="nofollow"
            v-text="value.name"
          />
          <div
            v-if="!value.selected && value.count"
            class="filter-badge"
            v-text="value.count"
          />
          <ul
            v-if="value.selected && value.items.length > 0"
            class="form-check subcategories"
          >
            <li
              v-for="subcategory in value.items"
              :key="subcategory.id"
              class="fl-dropdown-item"
              :class="{'form-check-label': !subcategory.selected}"
              @click.stop="close(); selected(getSubCategoryName(value, subcategory));"
            >
              <input
                :id="'option-' + subcategory.id"
                class="form-check-input hidden-xs-up"
                type="checkbox"
                :checked="subcategory.selected"
                :disabled="isLoading"
              >
              <label
                :for="'option-' + subcategory.id"
                :class="{'form-check-label': subcategory.selected}"
                rel="nofollow"
                v-text="subcategory.name"
              />
              <div
                v-if="!subcategory.selected && subcategory.count"
                class="filter-badge"
                v-text="subcategory.count"
              />
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</template>

<script lang="ts">
import BaseDropdown from '../../../mixins/baseDropdown';
import { FacetAware, FacetValue, PlentyVuexStore, TemplateOverridable } from '../../../shared/interfaces';
import { defineComponent, onMounted, ref } from '@vue/composition-api';
import UrlBuilder from '../../../shared/UrlBuilder';
import TranslationService from '../../../shared/TranslationService';

interface CategoryDropdownProps extends TemplateOverridable, FacetAware { }

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
    const getSubCategoryName = (parentCategory: FacetValue, subCategory: FacetValue): string => {
      return parentCategory.name + '_' + subCategory.name;
    };

    const dropdownLabel = ref('');

    onMounted(() => {
      dropdownLabel.value = buildDropdownLabel() as string;
    });

    return {
      dropdownLabel,
      getSubCategoryName,
      TranslationService
    };
  }
});
</script>
