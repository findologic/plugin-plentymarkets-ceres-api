<template>
  <div class="col-12">
    <h1 class="h2" id="searchPageTitle" v-html="infoData"></h1>
    <p class="smart-did-you-mean" v-if="smartText.length > 0" v-html="smartText"></p>
  </div>
</template>

<script lang="ts">
import { FacetAware, TemplateOverridable } from '../shared/interfaces';
import { defineComponent } from '@vue/composition-api';

interface SmartDidYouMeanProps extends TemplateOverridable, FacetAware {
  smartText: string;
  infoData: string;
}

export default defineComponent({
  name: 'SmartDidYouMean',
  props: {
    smartText: {
      type: String,
      default: '',
    },
    infoData: {
      type: String,
      default: '',
    },
  }
});
</script>

<style scoped>

</style>
