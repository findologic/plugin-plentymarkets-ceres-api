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
      <span class="fl-dropdown-label">{{ trans("Findologic::Template.pleaseSelect") }}</span>
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

interface ItemDropdownProps extends TemplateOverridable, FacetAware { }

export default defineComponent({
  name: 'ItemDropdown',
  mixins: [
      BaseDropdown
  ],
  setup: (props: ItemDropdownProps, { root }) => {
    root.$options.template = props.template || '#vue-item-dropdown';

    const trans = (key: string) => {
      return window.ceresTranslate(key);
    };

    return { trans };
  }
});
</script>

<style scoped lang="scss">
@import '../../../../../../../vendor/plentymarkets/plugin-ceres/resources/scss/ceres/variables';

.form-check-label {
  cursor: pointer;
}

.filter-badge {
  right: 0;
}

.fl-dropdown-container {
  cursor: pointer;
  position: relative;
  padding-left: 0;
  border: 2px solid $gray-200;
  margin-bottom: 2rem;

  .fl-dropdown-label {
    padding-left: 1rem;
  }

  .fl-dropdown-content {
    list-style-type: none;
    position: absolute;
    width: 100%;
    z-index: 2;
    background-color: $white;
    border: 1px solid #000;
    border-top: transparent;
    margin-top: 13px;
    padding-bottom: 1rem;
    max-height: 200px;
    overflow-y: auto;

    > .fl-dropdown-item {
      &:first-child {
        margin-top: 1rem;
      }
    }

    .fl-dropdown-item {
      cursor: pointer;

      label {
        cursor: pointer;
        margin-bottom: 0;
        padding-left: 1rem;
      }
    }
  }
}

#filterCollapse,
.main-filters {
  .list-controls & {
    .page-content .card .fl-dropdown-container .fl-dropdown-content .fl-dropdown-item.form-check-label .filter-badge {
      position: relative;
      top: 7px;
      float: right;
      right: 10px;
    }
  }
}
</style>
