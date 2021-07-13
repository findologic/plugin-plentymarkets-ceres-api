import Vuex from 'vuex';
import { shallowMount, createLocalVue } from '@vue/test-utils';
import { Facet } from '../../../shared/interfaces';
import FindologicItemFilter from './FindologicItemFilter.vue';
import VueCompositionAPI from '@vue/composition-api';

const localVue = createLocalVue();

localVue.use(Vuex);
localVue.use(VueCompositionAPI);

window.ceresTranslate = key => key;

describe('FindologicItemFilter', () => {

  let store: Store<State>;

  beforeEach(() => {
    store = new Vuex.Store({});
  });

  it('Shows the facet name', () => {
    const facet: Facet = {
      cssClass: '',
      findologicFilterType: 'select',
      id: 'test',
      isMain: false,
      itemCount: 3,
      name: 'Facet name',
      noAvailableFiltersText: '',
      select: 'multiple',
      type: '',
      values: [
        {
          count: 9,
          id: '20',
          image: '',
          items: [],
          name: '22220',
          position: 'item',
          selected: false
        },
        {
          count: 1,
          id: '21',
          image: '',
          items: [],
          name: '22221',
          position: 'item',
          selected: false
        }
      ]
    };

    store.state.itemList = {
      isLoading: false,
      selectedFacets: [],
      facets: [facet]
    };

    const wrapper = shallowMount(FindologicItemFilter, { propsData: { 'facet': facet, 'filtersPerRow': 3 }, store, localVue });

    expect(wrapper.element.querySelector(':scope > div.h3').innerHTML).toBe('Facet name');
  });
});
