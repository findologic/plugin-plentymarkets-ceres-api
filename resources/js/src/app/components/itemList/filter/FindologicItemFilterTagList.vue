<template>
  <div class="selected-filters clearfix">
    <div
      v-for="tag in tagList"
      :key="tag.id"
      :class="'selected-filter filter-' + tag.id"
      rel="nofollow"
      style="display: flex;"
      @click="removeTag(tag)"
    >
      <i
        class="fa fa-times mr-1 align-self-center"
        aria-hidden="true"
      /> 
      <p class="mb-0">
        {{ facetNames[tag.id] }}: {{ tag.name }}
      </p>
    </div>

    <div
      v-if="tagList.length >= 2"
      class="selected-filter reset-all"
      rel="nofollow"
      @click="resetAllTags()"
    >
      <p class="mb-0">
        {{ resetFilterText }}
      </p>
    </div>
  </div>
</template>

<script lang="ts">
import {
  computed,
  defineComponent,
  onMounted,
  ref,
} from '@vue/composition-api';
import {
  Facet,
  PlentyVuexStore,
  TemplateOverridable,
} from '../../../shared/interfaces';
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
    const tagList = ref<Facet[]>([]);
    const resetFilterText = ref<string>('');
    
    const facetNames = computed(() => {
      const map: { [key: string]: string } = {};

      store.state.itemList.facets.forEach((facet: Facet) => {
        map[facet.id] = facet.name as string;
      });

      return map;
    });

    onMounted(() => {
      resetFilterText.value = TranslationService.translate(
        'Ceres::Template.itemFilterReset'
      );
      tagList.value = UrlBuilder.getSelectedFilters(store);
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
      resetAllTags,
      resetFilterText,
    };
  },
});
</script>
