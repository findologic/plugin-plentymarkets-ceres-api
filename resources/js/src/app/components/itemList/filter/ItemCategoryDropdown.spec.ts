import Vuex from 'vuex';
import Vue from 'vue';
import { shallowMount, createLocalVue } from '@vue/test-utils';
import { CategoryFacet, State } from '../../../shared/interfaces';
import { Store } from 'vuex';
import ItemCategoryDropdown from './ItemCategoryDropdown.vue';
import VueCompositionAPI from '@vue/composition-api';
import UrlBuilder from '../../../shared/UrlBuilder';

const localVue = createLocalVue();

localVue.use(Vuex);
localVue.use(VueCompositionAPI);

window.ceresTranslate = key => key;

describe('ItemCategoryDropdown', () => {

    let store: Store<State>;

    beforeEach(() => {
        store = new Vuex.Store({});
    });

    it('shows all options in a dropdown regardless of set fixed item count', async () => {
        const facet: CategoryFacet = {
            cssClass: '',
            findologicFilterType: 'select',
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
            facets: [facet]
        };

        const wrapper = shallowMount(ItemCategoryDropdown, { propsData: { facet }, store, localVue });
        await Vue.nextTick();
        expect(wrapper.findAll(':scope > *').length).toBe(1);
        const options = wrapper.findAll(':scope > div.fl-category-dropdown-container.custom-select ul li');
        expect(options.length).toBe(2);
        expect(options.at(0).find('label').text()).toBe('Living Room');
        expect(options.at(1).find('label').text()).toBe('Office');
        expect(wrapper.find('.fl-dropdown-label').text()).toBe('Findologic::Template.pleaseSelect');
    });

    it('shows subcategories when the parent category is selected', async () => {
        const facet: CategoryFacet = {
            cssClass: '',
            findologicFilterType: 'select',
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
                    values: [
                        {
                            frequency: 2,
                            id: '5',
                            values: [],
                            translated: { name : 'Armchairs & Stools' },
                            selected: false
                        },
                        {
                            frequency: 2,
                            id: '6',
                            values: [],
                            translated: { name : 'Sofas' },
                            selected: false
                        }
                    ],
                    translated: { name : 'Living Room' },
                    selected: true
                }
            ]
        };

        store.state.itemList = {
            isLoading: false,
            selectedFacets: [],
            facets: [facet]
        };

        UrlBuilder.getSelectedFilters = jest.fn(() => [{ id: 'cat', name: 'Living Room' }]);

        const wrapper = shallowMount(ItemCategoryDropdown, { propsData: { facet }, store, localVue });
        await localVue.nextTick();

        const dropdownLabel = wrapper.find(':scope > div.fl-category-dropdown-container.custom-select > .fl-dropdown-label');
        expect(dropdownLabel.text()).toBe('Living Room');

        const categories = wrapper.findAll(':scope > div.fl-category-dropdown-container.custom-select > ul > li');
        expect(categories.length).toBe(1);
        expect(categories.at(0).find('label').text()).toBe('Living Room');

        const subcategories = categories.at(0).findAll('ul.subcategories li');
        expect(subcategories.length).toBe(2);
        expect(subcategories.at(0).find('label').text()).toBe('Armchairs & Stools');
        expect(subcategories.at(1).find('label').text()).toBe('Sofas');
    });

    it('does not show subcategories when no parent category is selected', async () => {
        const facet: CategoryFacet = {
            cssClass: '',
            findologicFilterType: 'select',
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
                    values: [
                        {
                            frequency: 2,
                            id: '5',
                            values: [],
                            translated: { name : 'Armchairs & Stools' },
                            selected: false
                        },
                        {
                            frequency: 2,
                            id: '6',
                            values: [],
                            translated: { name : 'Sofas' },
                            selected: false
                        }
                    ],
                    translated: { name : 'Living Room' },
                    selected: false
                },
                {
                    frequency: 4,
                    id: '5',
                    values: [
                        {
                            frequency: 2,
                            id: '8',
                            values: [],
                            translated: { name : 'Something' },
                            selected: false
                        },
                        {
                            frequency: 2,
                            id: '9',
                            values: [],
                            translated: { name : 'Something else' },
                            selected: false
                        }
                    ],
                    translated: { name : 'Not Living Room' },
                    selected: false
                }
            ]
        };

        store.state.itemList = {
            isLoading: false,
            selectedFacets: [],
            facets: [facet]
        };

        UrlBuilder.getSelectedFilters = jest.fn(() => []);

        const wrapper = shallowMount(ItemCategoryDropdown, { propsData: { facet }, store, localVue });
        await Vue.nextTick();

        const options = wrapper.findAll(':scope > div.fl-category-dropdown-container.custom-select > ul > li');
        expect(options.length).toBe(2);
        expect(options.at(0).find('label').text()).toBe('Living Room');
        expect(options.at(0).find('ul.subcategories li').exists()).toBeFalsy();
        expect(options.at(1).find('label').text()).toBe('Not Living Room');
        expect(options.at(1).find('ul.subcategories li').exists()).toBeFalsy();
    });
});
