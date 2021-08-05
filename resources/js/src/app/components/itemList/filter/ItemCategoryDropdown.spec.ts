import Vuex from 'vuex';
import { shallowMount, createLocalVue } from '@vue/test-utils';
import { CategoryFacet, State } from '../../../shared/interfaces';
import { Store } from 'vuex';
import ItemCategoryDropdown from './ItemCategoryDropdown.vue';
import VueCompositionAPI from '@vue/composition-api';

const localVue = createLocalVue();

localVue.use(Vuex);
localVue.use(VueCompositionAPI);

window.ceresTranslate = key => key;

describe('ItemCategoryDropdown', () => {

    let store: Store<State>;

    beforeEach(() => {
        store = new Vuex.Store({});
    });

    it('shows all options in a dropdown regardless of set fixed item count', () => {
        const facet: CategoryFacet = {
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
            facets: [facet]
        };

        const wrapper = shallowMount(ItemCategoryDropdown, { propsData: { facet }, store, localVue });

        expect(wrapper.findAll(':scope > *').length).toBe(1);
        const options = wrapper.findAll(':scope > div.fl-category-dropdown-container.custom-select ul li');
        expect(options.length).toBe(2);
        expect(options.at(0).find('label').text()).toBe('Living Room');
        expect(options.at(1).find('label').text()).toBe('Office');
        expect(wrapper.find('.fl-dropdown-label').text()).toBe('Findologic::Template.pleaseSelect');
    });

    it('shows subcategories when the parent category is selected', () => {
        const facet: CategoryFacet = {
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
                    items: [
                        {
                            count: 2,
                            id: '5',
                            items: [],
                            name: 'Armchairs & Stools',
                            selected: false
                        },
                        {
                            count: 2,
                            id: '6',
                            items: [],
                            name: 'Sofas',
                            selected: false
                        }
                    ],
                    name: 'Living Room',
                    selected: true
                }
            ]
        };

        store.state.itemList = {
            isLoading: false,
            selectedFacets: [],
            facets: [facet]
        };

        const wrapper = shallowMount(ItemCategoryDropdown, { propsData: { facet }, store, localVue });

        expect(wrapper.findAll(':scope > div.fl-category-dropdown-container.custom-select > ul > li').length).toBe(1);
        const subcategories = wrapper.findAll(':scope > div.fl-category-dropdown-container.custom-select > ul > li ul.subcategories li');
        expect(subcategories.length).toBe(2);
        expect(subcategories.at(0).find('label').text()).toBe('Armchairs & Stools');
        expect(subcategories.at(1).find('label').text()).toBe('Sofas');
        //TODO: check the dropdown text to be the selected category
    });

    it('does not show subcategories when no parent category is selected', () => {
        const facet: CategoryFacet = {
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
                    items: [
                        {
                            count: 2,
                            id: '5',
                            items: [],
                            name: '"Armchairs & Stools"',
                            selected: false
                        },
                        {
                            count: 2,
                            id: '6',
                            items: [],
                            name: 'Sofas',
                            selected: false
                        }
                    ],
                    name: 'Living Room',
                    selected: false
                },
                {
                    count: 4,
                    id: '5',
                    items: [
                        {
                            count: 2,
                            id: '8',
                            items: [],
                            name: 'Something',
                            selected: false
                        },
                        {
                            count: 2,
                            id: '9',
                            items: [],
                            name: 'Something else',
                            selected: false
                        }
                    ],
                    name: 'Not Living Room',
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

        const options = wrapper.findAll(':scope > div.fl-category-dropdown-container.custom-select > ul > li');
        expect(options.length).toBe(2);
        expect(options.at(0).find('label').text()).toBe('Living Room');
        expect(options.at(0).find('ul.subcategories li').exists()).toBeFalsy();
        expect(options.at(1).find('label').text()).toBe('Not Living Room');
        expect(options.at(1).find('ul.subcategories li').exists()).toBeFalsy();
    });
});
