import Vuex from 'vuex';
import { shallowMount, mount, createLocalVue } from '@vue/test-utils';
import { Facet, ColorFacet, State } from '../../../shared/interfaces';
import { Store } from 'vuex';
import FindologicItemFilter from './FindologicItemFilter.vue';
import VueCompositionAPI from '@vue/composition-api';
import UrlBuilder from '../../../shared/UrlBuilder';

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

    expect(wrapper.element.querySelector(':scope > div.h3')!.innerHTML).toBe('Facet name');
  });

  it('Shows the noAvailableFiltersText when facet has no values', () => {
    const dropdownFacet: Facet = {
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
    };

    const categoryFacet: Facet = {
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
    };

    const colorFacet: ColorFacet = {
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
    };

    store.state.itemList = {
      isLoading: false,
      selectedFacets: [],
      facets: [dropdownFacet, categoryFacet, colorFacet]
    };

    const dropdownWrapper = shallowMount(FindologicItemFilter, { propsData: { 'facet': dropdownFacet, 'filtersPerRow': 3 }, store, localVue });
    const categoryWrapper = shallowMount(FindologicItemFilter, { propsData: { 'facet': categoryFacet, 'filtersPerRow': 3 }, store, localVue });
    const colorWrapper = shallowMount(FindologicItemFilter, { propsData: { 'facet': colorFacet, 'filtersPerRow': 3 }, store, localVue });

    expect(dropdownWrapper.element.querySelector(':scope > div:last-child p')!.innerHTML).toBe('No values available for this dropdown');
    expect(categoryWrapper.element.querySelector(':scope > div:last-child p')!.innerHTML).toBe('No values available for this category dropdown');
    expect(colorWrapper.element.querySelector(':scope > div:last-child p')!.innerHTML).toBe('No values available for this color filter');
  });

  it('Renders values correctly in the respective filter containers', () => {
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

    const categoryFacet: Facet = {
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
          image: '',
          items: [],
          name: 'Living Room',
          position: 'item',
          selected: false
        },
        {
          count: 1,
          id: '21',
          image: '',
          items: [],
          name: 'Office',
          position: 'item',
          selected: false
        }
      ]
    };

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
          items: [],
          name: 'blue',
          position: 'item',
          selected: false
        },
        {
          colorImageUrl: 'https://plugin.demo.findologic.com/yellow.png',
          count: 5,
          hexValue: '#FF0000',
          id: '11',
          image: 'https://plugin.demo.findologic.com/yellow.png',
          items: [],
          name: 'red',
          position: 'item',
          selected: false
        },
        {
          name: 'Unknown',
          hexValue: null,
          id: '12',
          items: [],
          position: 'item',
          selected: false
        }
      ]
    };

    store.state.itemList = {
      isLoading: false,
      selectedFacets: [],
      facets: [dropdownFacet, categoryFacet, colorFacet]
    };

    const dropdownWrapper = mount(FindologicItemFilter, { propsData: { 'facet': dropdownFacet, 'filtersPerRow': 3 }, store, localVue });
    const dropdownElement = dropdownWrapper.element.querySelector(':scope > div:last-child .fl-dropdown')!;
    expect(dropdownElement.querySelectorAll('div.form-check').length).toBe(2);
    expect(dropdownElement.querySelector('div.form-check:first-child label.form-check-label')!.innerHTML).toBe('22220');
    expect(dropdownElement.querySelector('div.form-check:first-child .filter-badge')!.innerHTML).toBe('9');

    const categoryWrapper = mount(FindologicItemFilter, { propsData: { 'facet': categoryFacet, 'filtersPerRow': 3 }, store, localVue });
    const categoryElement = categoryWrapper.element.querySelector(':scope > div:last-child div.fl-category-dropdown-container')!;
    expect(categoryElement.querySelectorAll('ul.fl-dropdown-content li').length).toBe(2);
    expect(categoryElement.querySelector('ul.fl-dropdown-content li:first-child label')!.innerHTML).toBe('Living Room');
    expect(categoryElement.querySelector('ul.fl-dropdown-content li:first-child .filter-badge')!.innerHTML).toBe('4');

    const colorWrapper = mount(FindologicItemFilter, { propsData: { 'facet': colorFacet, 'filtersPerRow': 3 }, store, localVue });
    const colorElement = colorWrapper.element.querySelector(':scope > div:last-child .fl-item-color-tiles-container');
    expect(colorElement).toBeTruthy();
    expect(colorWrapper.element.querySelectorAll('ul.fl-item-color-tiles-list li.fl-item-color-tiles-list-item').length).toBe(3);
    const tileElement = colorWrapper.element.querySelector('li.fl-item-color-tiles-list-item:first-child .fl-color-tile-background')!;
    expect(tileElement).toBeTruthy();
  });

  it('Redirects to page with filter params applied after a filter value is clicked', async () => {
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
          image: '',
          items: [],
          name: '22220',
          position: 'item',
          selected: false
        }
      ]
    };

    const categoryFacet: Facet = {
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
          image: '',
          items: [],
          name: 'Living Room',
          position: 'item',
          selected: false
        }
      ]
    };

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
          colorImageUrl: 'https://plugin.demo.findologic.com/yellow.png',
          count: 5,
          hexValue: '#FF0000',
          id: '11',
          image: 'https://plugin.demo.findologic.com/yellow.png',
          items: [],
          name: 'red',
          position: 'item',
          selected: false
        }
      ]
    };

    store.state.itemList = {
      isLoading: false,
      selectedFacets: [],
      facets: [dropdownFacet, categoryFacet, colorFacet]
    };

    UrlBuilder.updateSelectedFilters = jest.fn();

    const dropdownWrapper = mount(FindologicItemFilter, { propsData: { 'facet': dropdownFacet, 'filtersPerRow': 3 }, store, localVue });
    const dropdownOptionElement = dropdownWrapper.find('div.form-check:first-child label.form-check-label');
    await dropdownOptionElement.trigger('click')
    expect(UrlBuilder.updateSelectedFilters).toHaveBeenNthCalledWith(1, dropdownFacet, 'test', '22220');

    const categoryWrapper = mount(FindologicItemFilter, { propsData: { 'facet': categoryFacet, 'filtersPerRow': 3 }, store, localVue });
    const categoryOptionElement = categoryWrapper.find('ul.fl-dropdown-content li');
    await categoryOptionElement.trigger('click')
    expect(UrlBuilder.updateSelectedFilters).toHaveBeenNthCalledWith(2, categoryFacet, 'cat', 'Living Room');

    const colorWrapper = mount(FindologicItemFilter, { propsData: { 'facet': colorFacet, 'filtersPerRow': 3 }, store, localVue });
    const colorOptionElement = colorWrapper.find('ul.fl-item-color-tiles-list li.fl-item-color-tiles-list-item label');
    await colorOptionElement.trigger('click')
    expect(UrlBuilder.updateSelectedFilters).toHaveBeenNthCalledWith(3, colorFacet, 'Color', 'red');
  });
});
