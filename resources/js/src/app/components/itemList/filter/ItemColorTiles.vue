<template>
  <div class="fl-item-color-tiles-container">
    <ul class="fl-item-color-tiles-list">
      <li
        v-for="colorValue in facet.values"
        :key="colorValue.id"
        class="fl-item-color-tiles-list-item"
      >
        <label
          class="fl-color-tile-label"
          rel="nofollow"
          @click="tileClicked(colorValue.name)"
        >
          <div
            class="fl-color-tile-background"
            :style="{backgroundColor: colorValue.hexValue, backgroundImage: 'url(' + colorValue.colorImageUrl + ')'}"
            :title="colorValue.name"
          >
            <img
              v-show="!colorValue.colorImageUrl && !colorValue.hexValue"
              class="fl-color-tile-image"
              src="/images/no-picture.png"
              :alt="colorValue.name"
            >
            <div
              v-show="colorValue.selected"
              class="fl-color-tile-selected-image"
            >
              <img
                class="fl-svg"
                data-src="https://cdn.findologic.com/login.symfony/web/autocomplete/img/selected.svg"
                data-fallback="https://cdn.findologic.com/login.symfony/web/autocomplete/img/selected.png"
              >
            </div>
          </div>
        </label>
      </li>
    </ul>
  </div>
</template>

<script lang="ts">
import { ColorFacet, FacetAware, FacetValue, TemplateOverridable } from '../../../shared/interfaces';
import UrlBuilder from '../../../shared/UrlBuilder';
import { defineComponent } from '@vue/composition-api';

interface ItemColorTilesProps extends TemplateOverridable, FacetAware {
  facet: ColorFacet;
}

export default defineComponent({
  name: 'ItemColorTiles',
  props: {
    facet: {
      type: Object,
      required: true
    },
  },
  setup: (props: ItemColorTilesProps) => {
    const isSelected = (facetValueName: string) => {
      const facetValue = props.facet.values.filter((value: FacetValue) => value.name === facetValueName);

      return facetValue.length && UrlBuilder.isValueSelected(props.facet, props.facet.id, facetValue[0].name);
    };
    const tileClicked = (value: string) => {
      UrlBuilder.updateSelectedFilters(props.facet, props.facet.id, value);
    };

    return {
      isSelected,
      tileClicked
    };
  }
});
</script>

<style scoped>

</style>
