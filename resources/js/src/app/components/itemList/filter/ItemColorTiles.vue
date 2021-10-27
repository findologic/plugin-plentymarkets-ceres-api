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

<script lang="ts">
import { ColorFacet, ColorFacetValue, FacetAware, TemplateOverridable } from '../../../shared/interfaces';
import UrlBuilder from '../../../shared/UrlBuilder';
import { defineComponent, nextTick, onMounted } from '@vue/composition-api';

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

      if (!colorValue.hexValue) {
        target.src = props.fallbackImage;
      } else {
        target.remove();
      }
    };

    const injectSvgImages = async () => {
      await nextTick();
      window.SVGInjector($('img.fl-svg'));
    };

    onMounted(injectSvgImages);

    return {
      tileClicked,
      handleImageError
    };
  }
});
</script>

<style scoped lang="scss">
@import '../../../../../../../vendor/plentymarkets/plugin-ceres/resources/scss/ceres/variables';

.fl-item-color-tiles-container {
  margin-bottom: 3rem;

  .fl-item-color-tiles-list {
    padding-left: 0;
    list-style-type: none;
    overflow: hidden;

    .fl-item-color-tiles-list-item {
      margin: 10px 10px 10px 0;
      float: left;

      .fl-color-tile-label {
        border: 1px solid $gray-200;
        position: relative;
        cursor: pointer;

        svg {
          width: 24px;
          height: 24px;
          margin: 3px;

          path {
            stroke: $black;
            fill: $white;
          }
        }
        .fl-color-tile-background {
          width: 30px;
          height: 30px;
          text-align: center;

          &:hover {
            border: 1px solid $custom-file-border-color;
          }
        }
        .fl-color-tile-image {
          position: absolute;
          left: 0;
          z-index: 1;
          width: 30px;
          height: 30px;
        }
        .fl-color-tile-selected-image {
          position: absolute;
          z-index: 2;
        }
      }
    }
  }
}
</style>
