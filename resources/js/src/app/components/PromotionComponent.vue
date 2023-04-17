<template>
  <div
    v-if="promotion.link !== '' && promotion.image !== ''"
    id="fl-promotion"
    class="page-content container-max test-promotion-block"
  >
    <a :href="promotion.link">
      <img
        class="image"
        :src="promotion.image"
      >
    </a>
  </div>
</template>

<script lang="ts">
import { defineComponent } from '@vue/composition-api';

export default defineComponent({
  name: 'PromotionComponent',
  props: {
    promotion: {
      type: Object,
      default: () => {
        return {
          link: '',
          image: ''
        };
      }
    }
  }
});
</script>
