<template>
  <!-- SSR:template(findologic-item-filter-image) -->
  <div>
    <div v-if="!facet.noAvailableFiltersText">
      <div
        v-for="value in facet.values"
        :key="value.id"
        class="form-check"
      >
        <input
          :id="'option-' + value.id"
          class="form-check-input hidden-xs-up"
          type="checkbox"
          :checked="value.selected"
          :disabled="isLoading"
          @change="updateFacet(value)"
        >
        <label
          :for="'option-' + value.id"
          class="form-check-label"
          rel="nofollow"
        >
          <img
            :src="value.imageUrl"
            :width="value.imageUrl ? '80px' : ''"
            @error="handleImageError($event, value)"
          >
          <span v-text="value.name" />
        </label>
        <div
          v-if="value.count"
          class="filter-badge"
          v-text="value.count"
        />
      </div>
    </div>
    <p
      v-if="facet.noAvailableFiltersText"
      v-text="facet.noAvailableFiltersText"
    />
  </div>
  <!-- /SSR -->
</template>

<script lang="ts" setup>
import { ColorFacet, ColorFacetValue, FacetAware, FacetValue, TemplateOverridable } from '../../../shared/interfaces';
import { computed, getCurrentInstance, nextTick, onMounted } from 'vue';
import UrlBuilder from '../../../shared/UrlBuilder';
import { SVGInjector } from '@tanem/svg-injector';

interface ItemFilterImageProps extends TemplateOverridable, FacetAware {
  facet: ColorFacet;
  fallbackImage: string;
}

const props = defineProps<ItemFilterImageProps>();
const root = getCurrentInstance()!.proxy;

const handleImageError = (event: Event, colorValue: ColorFacetValue): void => {
  const target = event.target as HTMLImageElement;

  if (!colorValue.hexValue) {
    target.src = props.fallbackImage;
  } else {
    target.remove();
  }
};

const isLoading = computed(() => root.$store.state.isLoading);

const updateFacet = (facetValue: FacetValue): void => {
  UrlBuilder.updateSelectedFilters(props.facet, props.facet.id, facetValue.name);
};

const injectSvgImages = async () => {
  await nextTick();
  SVGInjector(document.getElementsByClassName('fl-svg'));
};

onMounted(injectSvgImages);
</script>

<style scoped>

</style>
