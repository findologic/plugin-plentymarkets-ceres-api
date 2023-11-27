<template>
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
            :src="value.media.url"
            :width="value.media.url ? '80px' : ''"
            @error="handleImageError($event, value)"
          >
          <span v-text="value.translated.name" />
        </label>
        <div
          v-if="value.frequency"
          class="filter-badge"
          v-text="value.frequency"
        />
      </div>
    </div>
    <p
      v-if="facet.noAvailableFiltersText"
      v-text="facet.noAvailableFiltersText"
    />
  </div>
</template>

<script lang="ts">
import { ColorFacet, ColorFacetValue, FacetAware, FacetValue, TemplateOverridable } from '../../../shared/interfaces';
import { computed, defineComponent, nextTick, onMounted } from '@vue/composition-api';
import UrlBuilder from '../../../shared/UrlBuilder';
import { SVGInjector } from '@tanem/svg-injector';

interface ItemFilterImageProps extends TemplateOverridable, FacetAware {
  facet: ColorFacet;
  fallbackImage: string;
}

export default defineComponent({
  name: 'ItemFilterImage',
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
  selected : (value: string, other:any) =>{
        console.log({value, other});
        UrlBuilder.updateSelectedFilters(this.props.facet, this.props.facet.id, value);
    },
  setup: (props: ItemFilterImageProps, { root }) => {
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
      console.log('before call', {facetValue, facet: props.facet});
      UrlBuilder.updateSelectedFilters(props.facet, props.facet.id, facetValue.translated.name);
    };

    const injectSvgImages = async () => {
      await nextTick();
      SVGInjector(document.getElementsByClassName('fl-svg'));
    };

    onMounted(injectSvgImages);

    return {
      handleImageError,
      isLoading,
      updateFacet,
    };
  }
});
</script>

<style scoped>

</style>
