<template>
  <div id="fl-promotion" class="page-content container-max test-promotion-block" v-if="promotion.link !== '' && promotion.image !== ''">
    <a href="{{ promotion.link }}">
      <img class="image" src="{{ promotion.image }}" />
    </a>
  </div>
</template>

<script lang="ts">
import { FacetAware, TemplateOverridable } from '../shared/interfaces';
import { defineComponent } from '@vue/composition-api';
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
      type: Object
    }
  }
});
</script>

<style scoped>

</style>
