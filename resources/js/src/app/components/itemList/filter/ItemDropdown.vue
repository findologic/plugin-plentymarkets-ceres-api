<template>
  <div class="fl-dropdown">
    <div
      v-if="facet.values[0].frequency"
      v-for="value in facet.values"
      :key="value.id"
      class="form-check"
    >
      <input
        :id="'option-' + value.id"
        :disabled="isLoading"
        :checked="value.selected"
        class="form-check-input hidden-xs-up"
        type="checkbox"
        @change="selected(value.translated.name)"
        @click="selected(value.translated.name)"
      >
      <label
        :for="'option-' + value.id"
        class="form-check-label"
        rel="nofollow"
        v-text="value.translated.name"
      />
      <div
        v-if="value.frequency"
        class="filter-badge"
        v-text="value.frequency"
      />
    </div>
    <div
      v-if="facet.values.length && !facet.values[0].frequency"
      class="fl-dropdown-container custom-select"
      tabindex="0"
      @click="toggle()"
      @blur="close()"
    >
      <span class="fl-dropdown-label">{{ pleaseSelectText }}</span>
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
          @click="selected(value.translated.name)"
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
            v-text="value.translated.name"
          />
          <div
            v-if="value.frequency"
            class="filter-badge"
            v-text="value.frequency"
          />
        </li>
      </ul>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, onMounted, ref } from '@vue/composition-api';
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
    const pleaseSelectText = ref<string>('');
    
    root.$options.template = props.template || '#vue-item-dropdown';

    onMounted(() => {
      pleaseSelectText.value = TranslationService.translate('Findologic::Template.pleaseSelect');
    });

    return { pleaseSelectText };
  }
});
</script>

<style scoped>

</style>
