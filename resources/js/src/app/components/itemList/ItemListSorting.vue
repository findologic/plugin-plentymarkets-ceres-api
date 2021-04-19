<script lang="ts">
import Url from '../../mixins/url';
import Constants from '../../shared/constants';
import { Component, Mixins, Prop } from 'vue-property-decorator';
import Vue from 'vue';

const ItemListSortingProps = Vue.extend({
  props: {
    sortingList: {
      type: Array,
      required: true
    },
    defaultSorting: {
      type: String,
      required: true
    },
    template: {
      type: String,
      default: null
    }
  }
})

interface MixinInterface {
  content: string;
  setUrlParamValues: Function;
  defaultSorting: string;
  template: string;
}

@Component
export default class ItemListSorting extends Mixins<MixinInterface, Url>(ItemListSortingProps, Url) {

  get templateProp() {
    return this.template
  }

  get defaultSortingProp () {
    return this.defaultSorting
  }

  @Prop() private selectedSorting = {}

  created() {
    this.$options.template = this.templateProp || "#vue-item-list-sorting";
    this.setSelectedValue();
  }

  updateSorting() {
    this.setUrlParamValues([
      {
        key: Constants.PARAMETER_SORTING,
        value: this.selectedSorting
      },
      {
        key: Constants.PARAMETER_PAGE,
        value: 1
      }
    ]);
  }

  /**
   * Determine the initial value and set it in the vuex storage.
   */
  setSelectedValue() {
    const urlParams = this.getUrlParams(document.location.search);

    if (urlParams.sorting) {
      this.selectedSorting = urlParams.sorting;
    } else {
      this.selectedSorting = this.defaultSortingProp;
    }

    this.$store.commit("setItemListSorting", this.selectedSorting);
  }
}
</script>

<style scoped>

</style>
