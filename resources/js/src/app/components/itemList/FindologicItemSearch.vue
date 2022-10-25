<template>
  <form
    method="GET"
    action="/search"
    @submit="search()"
  >
    <div class="container-max" :class="{'p-0' : $ceres.isShopBuilder}">
      <div class="position-relative">
        <div class="d-flex flex-grow-1 position-relative my-2">
          <input
            type="search"
            class="search-input flex-grow-1 px-3 py-2"
            name="query"
            ref="searchInput"
            v-model="searchValue"
            :placeholder="$translate('Ceres::Template.headerSearchPlaceholder')"
            :aria-label="$translate('Ceres::Template.headerSearchTerm')"
          >

          <button class="search-submit px-3" type="submit" :aria-label="$translate('Ceres::Template.headerSearch')">
            <i class="fa fa-search"></i>
          </button>
        </div>
      </div>
    </div>
  </form>
</template>

<script lang="ts">
import { computed, defineComponent, onMounted, ref } from '@vue/composition-api';
import { TemplateOverridable } from '../../shared/interfaces';
import UrlBuilder from '../../shared/UrlBuilder';
import * as jQuery from 'jquery';

interface ItemSearchProps extends TemplateOverridable {
  showItemImages: boolean;
  forwardToSingleItem: boolean;
}

export default defineComponent({
  name: 'FindologicItemSearch',
  props: {
    template: {
      type: String,
      default: '#vue-item-search'
    },
    showItemImages: {
      type: Boolean,
      default: false
    },
    forwardToSingleItem: {
      type: Boolean,
      default: false
    }
  },
  setup: (props: ItemSearchProps, { root }) => {
    root.$options.template = props.template;

    const searchValue = ref('');

    const search = () => {
      console.log('trigger search here');
    };

    onMounted(() => {
      root.$nextTick(() => {
        const urlParams = UrlBuilder.getUrlParams(document.location.search);

        root.$store.commit('setItemListSearchString', urlParams.query);

        const rawQuery = urlParams.query ? urlParams.query as string : '';

        // Manually regex out all "+" signs as decodeURIComponent does not take care of that.
        // If we wouldn't replace them with spaces, "+" signs would be displayed in the search field.
        searchValue.value = decodeURIComponent(rawQuery.replace(/\+/g, ' '));
      });
    });

    return {
      searchValue,
      search,
    };
  }
});
</script>

<style scoped>

</style>
