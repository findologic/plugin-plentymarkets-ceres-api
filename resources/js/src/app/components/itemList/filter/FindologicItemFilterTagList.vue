<template>
  <div class="selected-filters clearfix">
    <span
      v-for="tag in tagList"
      :key="tag.id"
      :class="'selected-filter filter-' + tag.id"
      rel="nofollow"
      @click="removeTag(tag)"
    >
      <i
        class="fa fa-times"
        aria-hidden="true"
      /> {{ facetNames[tag.id] }}: {{ tag.name }}
    </span>

    <span
      v-if="tagList.length >= 2"
      class="selected-filter reset-all"
      rel="nofollow"
      @click="resetAllTags()"
    >
      {{ TranslationService.translate('Ceres::Template.itemFilterReset') }}
    </span>
  </div>
</template>

<script lang="ts">
import { computed, defineComponent, onBeforeMount, onMounted } from '@vue/composition-api';
import { Facet, PlentyVuexStore, TemplateOverridable } from '../../../shared/interfaces';
import UrlBuilder from '../../../shared/UrlBuilder';
import TranslationService from '../../../shared/TranslationService';

interface ItemFilterTagListProps extends TemplateOverridable {
  marginClasses: string;
  marginInlineStyles: string;
}

export default defineComponent({
  name: 'FindologicItemFilterTagList',
  props: {
    template: {
      type: String,
      default: '#vue-item-filter-tag-list',
    },
    marginClasses: {
      type: String,
      default: null,
    },
    marginInlineStyles: {
      type: String,
      default: null,
    },
  },
  setup: (props: ItemFilterTagListProps, { root }) => {
    root.$options.template = props.template || '#vue-item-filter-tag-list';
    const store = root.$store as PlentyVuexStore;

    const tagList = computed((): Facet[] => UrlBuilder.getSelectedFilters(store));
    const facetNames = computed(() => {
      const map: {[key: string]: string} = {};

      store.state.itemList.facets.forEach((facet: Facet) => {
        map[facet.id] = facet.name as string;
      });

      return map;
    });

    const removeTag = (tag: Facet) => {
      UrlBuilder.removeSelectedFilter(tag.id, tag?.name || '');
    };

    const resetAllTags = () => UrlBuilder.removeAllAttribsAndRefresh();

    return {
      tagList,
      facetNames,
      removeTag,
      TranslationService,
      resetAllTags
    };
  }
});
</script>

<style scoped>

</style>
