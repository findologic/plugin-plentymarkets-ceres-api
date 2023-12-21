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
          @click="tileClicked(colorValue.translated.name)"
        >
          <div
            class="fl-color-tile-background"
            :style="{backgroundColor: colorValue.colorHexCode}"
            :title="colorValue.translated.name"
          >
            <img
              v-if="!colorValue.media.url && !colorValue.colorHexCode"
              class="fl-color-tile-image"
              :alt="colorValue.translated.name"
              :src="fallbackImage"
            >
            <img
              v-else-if="colorValue.media.url"
              class="fl-color-tile-image"
              :src="colorValue.media.url"
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
</template>

<script lang="ts">
import { ColorFacet, ColorFacetValue, FacetAware, TemplateOverridable } from '../../../shared/interfaces';
import UrlBuilder from '../../../shared/UrlBuilder';
import { defineComponent, nextTick, onMounted } from '@vue/composition-api';
import { SVGInjector } from '@tanem/svg-injector';

interface ItemColorTilesProps extends TemplateOverridable, FacetAware {
  facet: ColorFacet;
  fallbackImage: string;
}

export default defineComponent({
  name: 'ItemColorTiles',
  props: {
    facet: {
      type: Object,
      required: true
    },
    fallbackImage: {
      type: String,
      default: ''
    }
  },
  setup: (props: ItemColorTilesProps) => {
    const tileClicked = (value: string) => {
      UrlBuilder.updateSelectedFilters(props.facet, props.facet.id, value);
    };
    const handleImageError = (event: Event, colorValue: ColorFacetValue): void => {
      const target = event.target as HTMLImageElement;

      if (!colorValue.colorHexCode) {
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

    return {
      tileClicked,
      handleImageError
    };
  }
});
</script>

<style scoped>

</style>
