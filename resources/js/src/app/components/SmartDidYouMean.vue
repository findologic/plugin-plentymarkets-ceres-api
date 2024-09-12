<template>
  <div class="col-12">
    <!-- eslint-disable vue/no-v-html -->
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
    <!--eslint-enable-->
  </div>
</template>

<script lang="ts">
import { defineComponent } from '@vue/composition-api';

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

