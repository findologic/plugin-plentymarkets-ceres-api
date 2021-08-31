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
          name: '22220',
          selected: false
        },
        {
          count: 1,
          id: '21',
          name: '22221',
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

    const filterHeading = wrapper.find(':scope > div.h3').text();
    expect(filterHeading).toBe('Facet name');
  });

  function noAvailableFiltersTextTestFacetProvider() {
    return [
      {
        facet: {
          cssClass: '',
          findologicFilterType: 'select',
          id: 'test',
          isMain: false,
          itemCount: 0,
          name: 'Facet name',
          noAvailableFiltersText: 'No values available for this dropdown',
          select: 'multiple',
          type: '',
          values: []
        } as Facet,
        expectedNoAvailableFiltersTextMessage: 'No values available for this dropdown'
      },
      {
        facet: {
          cssClass: '',
          findologicFilterType: 'select',
          id: 'cat',
          isMain: false,
          itemCount: 0,
          name: 'Category',
          noAvailableFiltersText: 'No values available for this category dropdown',
          select: 'single',
          type: '',
          values: []
        } as CategoryFacet,
        expectedNoAvailableFiltersTextMessage: 'No values available for this category dropdown'
      },
      {
        facet: {
          cssClass: '',
          findologicFilterType: 'color',
          id: 'Color',
          isMain: false,
          itemCount: 0,
          name: 'Color',
          noAvailableFiltersText: 'No values available for this color filter',
          select: 'multiselect',
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

    const wrapper = shallowMount(FindologicItemFilter, { propsData: { 'facet': facet, 'filtersPerRow': 3 }, store, localVue });

    const textInFilterElement = wrapper.find(':scope > div:last-child p').text();
    expect(textInFilterElement).toBe(data.expectedNoAvailableFiltersTextMessage);
  });

  it('Renders dropdown facet values correctly in the respective filter container', () => {
    const dropdownFacet: Facet = {
      cssClass: '',
      findologicFilterType: 'select',
      id: 'test',
      isMain: false,
      itemCount: 3,
      name: 'Test',
      noAvailableFiltersText: '',
      select: 'multiple',
      type: '',
      values: [
        {
          count: 9,
          id: '20',
          name: '22220',
          selected: false
        },
        {
          count: 1,
          id: '21',
          name: '22221',
          selected: false
        }
      ]
    };

    store.state.itemList = {
      isLoading: false,
      selectedFacets: [],
      facets: [dropdownFacet]
    };

    const dropdownWrapper = mount(FindologicItemFilter, { propsData: { 'facet': dropdownFacet, 'filtersPerRow': 3 }, store, localVue });
    const dropdownElement = dropdownWrapper.find(':scope > div:last-child .fl-dropdown');
    const dropdownOptions = dropdownElement.findAll('div.form-check');
    expect(dropdownOptions.length).toBe(2);
    const firstOptionLabel = dropdownOptions.at(0).find('label.form-check-label').text();
    expect(firstOptionLabel).toBe('22220');
    const firstOptionItemsCount = dropdownOptions.at(0).find('.filter-badge').text();
    expect(firstOptionItemsCount).toBe('9');
  });

  it('Renders category facet values correctly in the respective filter container', () => {
    const categoryFacet: CategoryFacet = {
      cssClass: '',
      findologicFilterType: 'select',
      id: 'cat',
      isMain: false,
      itemCount: 6,
      name: 'Category',
      noAvailableFiltersText: '',
      select: 'single',
      type: '',
      values: [
        {
          count: 4,
          id: '4',
          items: [],
          name: 'Living Room',
          selected: false
        },
        {
          count: 1,
          id: '21',
          items: [],
          name: 'Office',
          selected: false
        }
      ]
    };

    store.state.itemList = {
      isLoading: false,
      selectedFacets: [],
      facets: [categoryFacet]
    };

    const categoryWrapper = mount(FindologicItemFilter, { propsData: { 'facet': categoryFacet, 'filtersPerRow': 3 }, store, localVue });
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
      findologicFilterType: 'color',
      id: 'Color',
      isMain: false,
      itemCount: 8,
      name: 'Color',
      noAvailableFiltersText: '',
      select: 'multiselect',
      type: '',
      values: [
        {
          count: 4,
          hexValue: '#0000FF',
          id: '10',
          name: 'blue',
          selected: false
        },
        {
          colorImageUrl: 'https://plugin.demo.findologic.com/yellow.png',
          count: 5,
          hexValue: '#FF0000',
          id: '11',
          name: 'red',
          selected: false
        },
        {
          name: 'Unknown',
          hexValue: null,
          id: '12',
          selected: false
        }
      ]
    };

    store.state.itemList = {
      isLoading: false,
      selectedFacets: [],
      facets: [colorFacet]
    };

    const colorWrapper = mount(FindologicItemFilter, { propsData: { 'facet': colorFacet, 'filtersPerRow': 3 }, store, localVue });
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
          findologicFilterType: 'select',
          id: 'test',
          isMain: false,
          itemCount: 3,
          name: 'Test',
          noAvailableFiltersText: '',
          select: 'multiple',
          type: '',
          values: [
            {
              count: 9,
              id: '20',
              name: '22220',
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
          findologicFilterType: 'select',
          id: 'cat',
          isMain: false,
          itemCount: 6,
          name: 'Category',
          noAvailableFiltersText: '',
          select: 'single',
          type: '',
          values: [
            {
              count: 4,
              id: '4',
              items: [],
              name: 'Living Room',
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
          findologicFilterType: 'color',
          id: 'Color',
          isMain: false,
          itemCount: 8,
          name: 'Color',
          noAvailableFiltersText: '',
          select: 'multiselect',
          type: '',
          values: [
            {
              colorImageUrl: 'https://plugin.demo.findologic.com/yellow.png',
              count: 5,
              hexValue: '#FF0000',
              id: '11',
              name: 'red',
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

  it.each(filterClickRedirectionTestProvider())('Redirects to page with filter params applied after a filter value is clicke', async (data) => {
    const facet = data.facet;

    store.state.itemList = {
      isLoading: false,
      selectedFacets: [],
      facets: [facet]
    };

    UrlBuilder.updateSelectedFilters = jest.fn();

    const wrapper = mount(FindologicItemFilter, { propsData: { facet, 'filtersPerRow': 3 }, store, localVue });
    const optionElement = wrapper.find(data.itemSelector);
    await optionElement.trigger('click');
    expect(UrlBuilder.updateSelectedFilters).toHaveBeenNthCalledWith(1, facet, data.expectedFacetId, data.expectedFacetValue);
  });
});
