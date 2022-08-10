<template>
  <!-- SSR:template(findologic-item-dropdown) -->
  <div class="fl-dropdown">
    <div
      v-for="value in facet.values.slice(0, facet.itemCount)"
      :key="value.id"
      class="form-check"
    >
      <input
        :id="'option-' + value.id"
        :disabled="isLoading"
        :checked="value.selected"
        class="form-check-input hidden-xs-up"
        type="checkbox"
        @change="selected(value.name)"
        @click="selected(value.name)"
      >
      <label
        :for="'option-' + value.id"
        class="form-check-label"
        rel="nofollow"
        v-text="value.name"
      />
      <div
        v-if="value.count"
        class="filter-badge"
        v-text="value.count"
      />
    </div>
    <div
      v-if="facet.values.slice(facet.itemCount, facet.values.length).length"
      class="fl-dropdown-container custom-select"
      tabindex="0"
      @click="toggle()"
      @blur="close()"
    >
      <span class="fl-dropdown-label">{{ TranslationService.flTranslate("pleaseSelect") }}</span>
      <ul
        v-show="isOpen"
        class="fl-dropdown-content form-check"
      >
        <li
          v-for="value in facet.values.slice(facet.itemCount, facet.values.length)"
          :key="value.id"
          class="fl-dropdown-item"
          :class="{'form-check-label': !value.selected}"
          rel="nofollow"
          @click="selected(value.name)"
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
            v-if="value.count"
            class="filter-badge"
            v-text="value.count"
          />
        </li>
      </ul>
    </div>
  </div>
  <!-- /SSR -->
</template>

<script lang="ts">
import { defineComponent } from '@vue/composition-api';
import { FacetAware, TemplateOverridable } from '../../../shared/interfaces';
import BaseDropdown from '../../../mixins/baseDropdown';
import TranslationService from '../../../shared/TranslationService';

interface ItemDropdownProps extends TemplateOverridable, FacetAware { }

export default defineComponent({
  name: 'ItemDropdown',
  mixins: [
      BaseDropdown
  ],
  setup: (props: ItemDropdownProps, { root }) => {
    root.$options.template = props.template || '#vue-item-dropdown';

    return { TranslationService };
  }
});
</script>

<style scoped>

</style>
