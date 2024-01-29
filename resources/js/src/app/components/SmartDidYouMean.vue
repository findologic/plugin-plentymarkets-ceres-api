<template>
  <div class="col-12">
    <h1
      id="searchPageTitle"
      class="h2"
      v-html="queryInfoMessage.query"
    />
    <p
      v-if="smartText.type"
      class="smart-did-you-mean"
      v-html="smartText[`${smartText.type}Query`]"
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
  },
  onMounted : (props)=>{
    console.log('smartText', this.smartText)
    console.log('props', props)
  }
});
</script>

