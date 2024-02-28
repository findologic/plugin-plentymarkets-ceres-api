import Vue from 'vue';
import Vuex from 'vuex';
import { shallowMount, mount, createLocalVue } from '@vue/test-utils';
import { Facet, ColorFacet, State, CategoryFacet } from '../../../shared/interfaces';
import { Store } from 'vuex';
import FindologicItemFilter from './FindologicItemFilter.vue';
import VueCompositionAPI from '@vue/composition-api';
import UrlBuilder from '../../../shared/UrlBuilder';
import $ from 'jquery';

const localVue = createLocalVue();

localVue.use(Vuex);
localVue.use(VueCompositionAPI);

window.ceresTranslate = key => key;
window.$ = $;
window.SVGInjector = jest.fn;

describe('FindologicItemFilter', () => {

  let store: Store<State>;

  beforeEach(() => {
    store = new Vuex.Store({});
  });

  it('Shows the facet name', async () => {
    const facet: Facet = {
      cssClass: '',
      findologicFilterType: 'selectFilter',
      id: 'test',
      isMain: false,
      itemCount: 3,
      name: 'Facet name',
      noAvailableFiltersText: '',
      selectMode: 'multiple',
      type: '',
      values: [
        {
          frequency: 9,
          id: '20',
          translated: { name : '22220' },
          selected: false,
          values: []
        },
        {
          frequency: 1,
          id: '21',
          translated: { name : '22221' },
          selected: false,
          values: []
        }
      ]
    };

    store.state.itemList = {
      isLoading: false,
      selectedFacets: [],
      facets: [facet]
    };

    const wrapper = shallowMount(FindologicItemFilter, {
      stubs: { 'client-only': true },
      propsData: {
        'facet': facet,
        'filtersPerRow': 3
      },
      store,
      localVue
    });

    await Vue.nextTick();

    const filterHeading = wrapper.find(':scope > div div.h3').text();
    expect(filterHeading).toBe('Facet name');
  });

  function noAvailableFiltersTextTestFacetProvider() {
    return [
      {
        facet: {
          cssClass: '',
          findologicFilterType: 'selectFilter',
          id: 'test',
          isMain: false,
          itemCount: 0,
          name: 'Facet name',
          noAvailableFiltersText: 'No values available for this dropdown',
          selectMode: 'multiple',
          type: '',
          values: []
        } as Facet,
        expectedNoAvailableFiltersTextMessage: 'No values available for this dropdown'
      },
      {
        facet: {
          cssClass: '',
          findologicFilterType: 'selectFilter',
          id: 'cat',
          isMain: false,
          itemCount: 0,
          name: 'Category',
          noAvailableFiltersText: 'No values available for this category dropdown',
          selectMode: 'single',
          type: '',
          values: []
        } as CategoryFacet,
        expectedNoAvailableFiltersTextMessage: 'No values available for this category dropdown'
      },
      {
        facet: {
          cssClass: '',
          findologicFilterType: 'colorPickerFilter',
          id: 'Color',
          isMain: false,
          itemCount: 0,
          name: 'Color',
          noAvailableFiltersText: 'No values available for this color filter',
          selectMode: 'multiselect',
          type: '',
          values: []
        } as Facet,
        expectedNoAvailableFiltersTextMessage: 'No values available for this color filter'
      }
    ];
  }

  it.each(noAvailableFiltersTextTestFacetProvider())('Shows the noAvailableFiltersText when facet has no values', (data) => {
    const facet: Facet = data.facet;

    store.state.itemList = {
      isLoading: false,
      selectedFacets: [],
      facets: [facet]
    };

    const wrapper = shallowMount(FindologicItemFilter, {
      stubs: { 'client-only': true },
      propsData: {
        'facet': facet,
        'filtersPerRow': 3
      },
      store,
      localVue
    });

    const textInFilterElement = wrapper.find(':scope > div:last-child p').text();
    expect(textInFilterElement).toBe(data.expectedNoAvailableFiltersTextMessage);
  });

  it('Renders dropdown facet values correctly in the respective filter container', () => {
    const dropdownFacet: Facet = {
      cssClass: '',
      findologicFilterType: 'selectFilter',
      id: 'test',
      isMain: false,
      itemCount: 3,
      name: 'Test',
      noAvailableFiltersText: '',
      selectMode: 'multiple',
      type: '',
      values: [
        {
          frequency: 9,
          id: '20',
          translated: { name : '22220' },
          selected: false,
          values: []
        },
        {
          frequency: 1,
          id: '21',
          translated: { name : '22221' },
          selected: false,
          values: []
        }
      ]
    };

    store.state.itemList = {
      isLoading: false,
      selectedFacets: [],
      facets: [dropdownFacet]
    };

    const dropdownWrapper = mount(FindologicItemFilter, {
      stubs: { 'client-only': true },
      propsData: {
        'facet': dropdownFacet,
        'filtersPerRow': 3
      },
      store,
      localVue
    });
    const dropdownElement = dropdownWrapper.find(':scope > div:last-child .fl-dropdown');
    const dropdownOptions = dropdownElement.findAll('div.form-check');
    expect(dropdownOptions.length).toBe(2);
    const firstOptionLabel = dropdownOptions.at(0).find('label.form-check-label').text();
    expect(firstOptionLabel).toBe('22220');
    const firstOptionItemsCount = dropdownOptions.at(0).find('.filter-badge').text();
    expect(firstOptionItemsCount).toBe('9');
  });

  it('Renders category facet values correctly in the respective filter container', async () => {
    const categoryFacet: CategoryFacet = {
      cssClass: '',
      findologicFilterType: 'selectFilter',
      id: 'cat',
      isMain: false,
      itemCount: 6,
      name: 'Category',
      noAvailableFiltersText: '',
      selectMode: 'single',
      type: '',
      values: [
        {
          frequency: 4,
          id: '4',
          values: [],
          translated: { name : 'Living Room' },
          selected: false
        },
        {
          frequency: 1,
          id: '21',
          values: [],
          translated: { name : 'Office' },
          selected: false
        }
      ]
    };

    store.state.itemList = {
      isLoading: false,
      selectedFacets: [],
      facets: [categoryFacet]
    };

    const categoryWrapper = mount(FindologicItemFilter, {
      stubs: { 'client-only': true },
      propsData: {
        'facet': categoryFacet,
        'filtersPerRow': 3
      },
      store,
      localVue
    });
    await Vue.nextTick();

    const categoryElement = categoryWrapper.find(':scope > div:last-child div.fl-category-dropdown-container');
    const dropdownOptions = categoryElement.findAll('ul.fl-dropdown-content li');
    expect(dropdownOptions.length).toBe(2);
    const firstOptionLabel = dropdownOptions.at(0).find('label').text();
    expect(firstOptionLabel).toBe('Living Room');
    const firstOptionItemsCount = dropdownOptions.at(0).find('.filter-badge').text();
    expect(firstOptionItemsCount).toBe('4');
  });

  it('Renders color facet values correctly in the respective filter container', () => {
    const colorFacet: ColorFacet = {
      cssClass: '',
      findologicFilterType: 'colorPickerFilter',
      id: 'Color',
      isMain: false,
      itemCount: 8,
      name: 'Color',
      noAvailableFiltersText: '',
      selectMode: 'multiselect',
      type: '',
      values: [
        {
          frequency: 4,
          colorHexCode: '#0000FF',
          id: '10',
          translated: { name : 'blue' },
          selected: false,
          values: []
        },
        {
          colorImageUrl: 'https://plugin.demo.findologic.com/yellow.png',
          frequency: 5,
          colorHexCode: '#FF0000',
          id: '11',
          translated: { name : 'red' },
          selected: false,
          values: []
        },
        {
          translated: { name : 'Unknown' },
          colorHexCode: null,
          id: '12',
          selected: false,
          values: []
        }
      ]
    };

    store.state.itemList = {
      isLoading: false,
      selectedFacets: [],
      facets: [colorFacet]
    };

    const colorWrapper = mount(FindologicItemFilter, {
      stubs: { 'client-only': true },
      propsData: {
        'facet': colorFacet,
        'filtersPerRow': 3
      },
      store,
      localVue
    });
    const colorFilterElement = colorWrapper.find(':scope > div:last-child .fl-item-color-tiles-container');
    expect(colorFilterElement.exists()).toBeTruthy();
    const colorTiles = colorWrapper.findAll('ul.fl-item-color-tiles-list li.fl-item-color-tiles-list-item');
    expect(colorTiles.length).toBe(3);
    expect(colorTiles.at(0).find('.fl-color-tile-background[title="blue"]').exists()).toBeTruthy();
    expect(colorTiles.at(1).find('.fl-color-tile-background[title="red"]').exists()).toBeTruthy();
    expect(colorTiles.at(2).find('.fl-color-tile-background[title="Unknown"]').exists()).toBeTruthy();
  });

  function filterClickRedirectionTestProvider() {
    return [
      {
        facet: {
          cssClass: '',
          findologicFilterType: 'selectFilter',
          id: 'test',
          isMain: false,
          itemCount: 3,
          name: 'Test',
          noAvailableFiltersText: '',
          selectMode: 'multiple',
          type: '',
          values: [
            {
              frequency: 9,
              id: '20',
              translated : { name: '22220' },
              selected: false
            }
          ]
        } as Facet,
        itemSelector: 'div.form-check:first-child label.form-check-label',
        expectedFacetId: 'test',
        expectedFacetValue: '22220'
      },
      {
        facet: {
          cssClass: '',
          findologicFilterType: 'selectFilter',
          id: 'cat',
          isMain: false,
          itemCount: 6,
          name: 'Category',
          noAvailableFiltersText: '',
          selectMode: 'single',
          type: '',
          values: [
            {
              frequency: 4,
              id: '4',
              values: [],
              translated: { name : 'Living Room' },
              selected: false
            }
          ]
        } as CategoryFacet,
        itemSelector: 'ul.fl-dropdown-content li',
        expectedFacetId: 'cat',
        expectedFacetValue: 'Living Room'
      },
      {
        facet: {
          cssClass: '',
          findologicFilterType: 'colorPickerFilter',
          id: 'Color',
          isMain: false,
          itemCount: 8,
          name: 'Color',
          noAvailableFiltersText: '',
          selectMode: 'multiselect',
          type: '',
          values: [
            {
              colorImageUrl: 'https://plugin.demo.findologic.com/yellow.png',
              frequency: 5,
              colorHexCode: '#FF0000',
              id: '11',
              translated: { name : 'red' },
              selected: false
            }
          ]
        } as ColorFacet,
        itemSelector: 'ul.fl-item-color-tiles-list li.fl-item-color-tiles-list-item label',
        expectedFacetId: 'Color',
        expectedFacetValue: 'red'
      }
    ];
  }

  it.each(filterClickRedirectionTestProvider())('Redirects to page with filter params applied after a filter value is clicked', async (data) => {
    const facet = data.facet;

    store.state.itemList = {
      isLoading: false,
      selectedFacets: [],
      facets: [facet]
    };

    UrlBuilder.updateSelectedFilters = jest.fn();

    const wrapper = mount(FindologicItemFilter, {
      stubs: { 'client-only': true },
      propsData: {
        facet,
        'filtersPerRow': 3
      },
      store,
      localVue
    });
    await Vue.nextTick();
    
    const optionElement = wrapper.find(data.itemSelector);
    await optionElement.trigger('click');
    expect(UrlBuilder.updateSelectedFilters).toHaveBeenNthCalledWith(1, facet, data.expectedFacetId, data.expectedFacetValue);
  });
});
