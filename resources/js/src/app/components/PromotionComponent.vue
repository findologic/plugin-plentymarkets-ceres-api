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
import { FacetAware, TemplateOverridable } from '../shared/interfaces';
import { defineComponent } from 'vue';
import {
  Promotion
} from '../shared/interfaces';

interface PromotionComponentProps extends TemplateOverridable, FacetAware {
  promotion: Promotion;
}

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
