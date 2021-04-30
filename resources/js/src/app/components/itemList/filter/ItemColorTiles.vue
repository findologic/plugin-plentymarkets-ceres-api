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
import { Mixins } from "vue-property-decorator";
import Component from "vue-class-component";
import Vue from 'vue';
import { ColorFacet } from '../../../shared/interfaces';
import UrlBuilder from '../../../shared/UrlBuilder';

const ItemColorTileProps = Vue.extend({
  props: {
    facet: {
      type: Object,
      required: true
    }
  }
})

interface ItemColorTilePropsInterface {
  facet: ColorFacet;
}

@Component({
  computed: {
    isLoading() {
      return this.$store.state.itemList.isLoading
    }
  }
})
export default class ItemColorTiles extends Mixins<ItemColorTilePropsInterface>(ItemColorTileProps) {
  get facetData(): ColorFacet {
    return this.facet;
  }

  isSelected(facetValueName: string) {
    const facetValue = this.facetData.values.filter((value) => value.name === facetValueName);

    return facetValue.length && UrlBuilder.isValueSelected(this.facet, this.facetData.id, facetValue[0].name);
  }

  tileClicked(value: string) {
    UrlBuilder.updateSelectedFilters(this.facet, this.facetData.id, value);
  }
}
</script>

<style scoped>

</style>
