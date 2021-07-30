<template>
  <div class="fl-range-slider-container">
    <div class="row">
      <div class="col-md-6 col-xs-6">
        <input
          v-model="valueFrom"
          class="fl-range-input"
        >
        <span
          class="fl-unit"
          v-text="facet.unit"
        />
      </div>
      <div class="col-md-6 col-xs-6">
        <input
          v-model="valueTo"
          class="fl-range-input"
        >
        <span
          class="fl-unit"
          v-text="facet.unit"
        />
      </div>
    </div>
    <div class="row fl-range-slider-row">
      <div class="col-md-9 col-sm-9 col-xs-12">
        <div
          :id="sanitizedFacetId"
          class="fl-range-slider"
        />
      </div>
      <div class="col-md-3 col-sm-3 col-xs-12 fl-range-slider-submit-btn-container">
        <button
          v-tooltip
          type="button"
          class="btn btn-primary fl-range-slider-submit-btn"
          :class="{'disabled': isDisabled}"
          data-toggle="tooltip"
          data-placement="top"
          :title="TranslationService.translate('Ceres::Template.itemApply')"
          rel="nofollow"
          @click="triggerFilter()"
        >
          <icon
            icon="fa-check"
            :loading="isLoading"
          />
        </button>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { FacetAware, TemplateOverridable } from '../../../shared/interfaces';
import { computed, defineComponent, onMounted, ref } from '@vue/composition-api';
import UrlBuilder, { PriceFacetValue } from '../../../shared/UrlBuilder';
import TranslationService from '../../../shared/TranslationService';

interface ItemRangeSliderProps extends TemplateOverridable, FacetAware { }

export default defineComponent({
  name: 'ItemRangeSlider',
  props: {
    facet: {
      type: Object,
      required: true
    }
  },
  setup: (props: ItemRangeSliderProps, { root }) => {
    const valueFrom = ref();
    const valueTo = ref();
    const facet = props.facet;

    const values = UrlBuilder.getSelectedFilterValue(props.facet.id);
    valueFrom.value = (values ? values.min : props.facet.minValue) || '';
    valueTo.value = (values ? values.max : props.facet.maxValue) || '';

    const isLoading = computed(() => root.$store.state.isLoading);
    const sanitizedFacetId = computed(() => {
      return 'fl-range-slider-' + props.facet.id
          .replace(/\W/g, '-')
          .replace(/-+/, '-')
          .replace(/-$/, '');
    });
    const isDisabled = computed(() => {
      return (valueFrom.value === '' && valueTo.value === '') ||
          (parseFloat(valueFrom.value) > parseFloat(valueTo.value)) ||
          root.$store.state.isLoading;
    });

    const triggerFilter = () => {
      if (!isDisabled.value) {
        const facetValue = {
          min: parseFloat(valueFrom.value),
          max: valueTo.value ? parseFloat(valueTo.value) : Number.MAX_SAFE_INTEGER
        } as PriceFacetValue;

        UrlBuilder.updateSelectedFilters(facet, facet.id, facetValue);
      }
    };

    onMounted(() => {
      $(document).ready(function () {
        const element = document.getElementById(sanitizedFacetId.value);

        const slider = window.noUiSlider.create(element, {
          step: props.facet.step,
          start: [valueFrom.value, valueTo.value],
          connect: true,
          range: {
            'min': props.facet.minValue,
            'max': props.facet.maxValue
          }
        });

        slider.on('update', function (ui: string[]) {
          valueFrom.value = ui[0];
          valueTo.value = ui[1];
        });
      });
    });

    return {
      valueFrom,
      valueTo,
      sanitizedFacetId,
      isDisabled,
      isLoading,
      triggerFilter,
      TranslationService
    };
  }
});
</script>

<style scoped>

</style>
