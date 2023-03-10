<template>
  <div class="col-12">
    <h1
      id="searchPageTitle"
      class="h2"
      v-html="queryInfoMessage"
    />
    <p
      v-if="smartText.length > 0"
      class="smart-did-you-mean"
      v-html="smartText"
    />
  </div>
</template>

<script lang="ts">
import { FacetAware, TemplateOverridable } from '../shared/interfaces';
import { defineComponent } from 'vue';

interface SmartDidYouMeanProps extends TemplateOverridable, FacetAware {
  smartText: string;
  queryInfoMessage: string;
}

export default defineComponent({
  name: 'SmartDidYouMean',
  props: {
    smartText: {
      type: String,
      default: '',
    },
    queryInfoMessage: {
      type: String,
      default: '',
    },
  }
});
</script>

