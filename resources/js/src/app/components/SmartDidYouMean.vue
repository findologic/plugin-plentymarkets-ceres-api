<template>
  <div class="col-12">
    <h1
      id="searchPageTitle"
      class="h2"
      v-html="queryInfoMessage.query"
    />
    <p
      v-if="smartText.length > 0"
      class="smart-did-you-mean"
      v-html="smartText[smartText.type]"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent } from '@vue/composition-api';

export default defineComponent({
  name: 'SmartDidYouMean',
  props: {
    smartText: {
      type: Object,
      default: () => {
        return {
          type: '',
          didYouMeanQuery: '',
          improvedQuery: '',
          correctedQuery: ''
        };
      }
    },
    queryInfoMessage: {
      type: Object,
      default: () => {
        return {
          query: '',
        };
      },
    },
  }
});
</script>

