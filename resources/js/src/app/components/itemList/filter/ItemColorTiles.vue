<template>
  <!-- SSR:template(findologic-item-color-tiles) -->
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
            :style="{backgroundColor: colorValue.hexValue}"
            :title="colorValue.name"
          >
            <img
              v-if="!colorValue.colorImageUrl && !colorValue.hexValue"
              class="fl-color-tile-image"
              :alt="colorValue.name"
              :src="fallbackImage"
            >
            <img
              v-else-if="colorValue.colorImageUrl"
              class="fl-color-tile-image"
              :src="colorValue.colorImageUrl"
              @error="handleImageError($event, colorValue)"
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
  <!-- /SSR -->
</template>

<script lang="ts" setup>
import { ColorFacet, ColorFacetValue, FacetAware, TemplateOverridable } from '../../../shared/interfaces';
import UrlBuilder from '../../../shared/UrlBuilder';
import { nextTick, onMounted } from 'vue';
import { SVGInjector } from '@tanem/svg-injector';

interface ItemColorTilesProps extends TemplateOverridable, FacetAware {
  facet: ColorFacet;
  fallbackImage: string;
}
const props = defineProps<ItemColorTilesProps>();
const tileClicked = (value: string) => {
    UrlBuilder.updateSelectedFilters(props.facet, props.facet.id, value);
  };
const handleImageError = (event: Event, colorValue: ColorFacetValue): void => {
  const target = event.target as HTMLImageElement;

  if (!colorValue.hexValue) {
    target.src = props.fallbackImage;
  } else {
    target.remove();
  }
};

const injectSvgImages = async () => {
  await nextTick();
  SVGInjector(document.getElementsByClassName('fl-svg'));
};

onMounted(injectSvgImages);
</script>

<style scoped>

</style>
