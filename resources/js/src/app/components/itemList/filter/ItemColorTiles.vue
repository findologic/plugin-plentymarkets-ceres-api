<template>
  <div class="fl-item-color-tiles-container">
    <ul class="fl-item-color-tiles-list">
      <li class="fl-item-color-tiles-list-item"
          v-for="colorValue in facet.values"
          :key="colorValue.id"
      >
        <label class="fl-color-tile-label" @click="tileClicked(colorValue.name)" rel="nofollow">
          <div class="fl-color-tile-background"
               :style="{backgroundColor: colorValue.hexValue, backgroundImage: 'url(' + colorValue.colorImageUrl + ')'}"
               :title="colorValue.name"
          >
            <img class="fl-color-tile-image"
                 v-show="!colorValue.colorImageUrl && !colorValue.hexValue"
                 src="/images/no-picture.png"
                 :alt="colorValue.name"
            />
            <div class="fl-color-tile-selected-image" v-show="colorValue.selected">
              <img class="fl-svg"
                   data-src="https://cdn.findologic.com/login.symfony/web/autocomplete/img/selected.svg"
                   data-fallback="https://cdn.findologic.com/login.symfony/web/autocomplete/img/selected.png"/>
            </div>
          </div>
        </label>
      </li>
    </ul>
  </div>
</template>

<script lang="ts">
import Url from "../../../mixins/url";
import { Mixins } from "vue-property-decorator";
import Component from "vue-class-component";
import Vue from 'vue';
import Vuex from 'vuex';
import { ColorFacet } from '../../../interfaces';

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
    // @ts-ignore
    ...Vuex.mapState({
      // @ts-ignore
      isLoading: state => state.itemList.isLoading,
    })
  }
})
export default class ItemColorTiles extends Mixins<ItemColorTilePropsInterface, Url>(ItemColorTileProps, Url) {
  get facetData(): ColorFacet {
    return this.facet;
  }

  isSelected(facetValueName: string) {
    const facetValue = this.facetData.values.filter((value) => value.name === facetValueName);

    return facetValue.length && this.isValueSelected(this.facetData.id, facetValue[0].name);
  }

  tileClicked(value: string) {
    this.updateSelectedFilters(this.facetData.id, value);
  }
}
</script>

<style scoped>

</style>
