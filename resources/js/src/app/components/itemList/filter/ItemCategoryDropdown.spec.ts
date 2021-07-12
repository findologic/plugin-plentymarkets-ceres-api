import Vuex from 'vuex';
import { shallowMount, createLocalVue } from '@vue/test-utils';
import { Facet } from '../../../shared/interfaces';
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
        const facet: Facet = {
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

        store.state.itemList = {
            isLoading: false,
            selectedFacets: [],
            facets: [facet]
        };

        const wrapper = shallowMount(ItemCategoryDropdown, { propsData: { facet }, store, localVue });

        expect(wrapper.element.querySelectorAll(':scope > *').length).toBe(1);
        expect(wrapper.element.querySelectorAll(':scope > div.fl-category-dropdown-container.custom-select ul li').length).toBe(2);
        expect(wrapper.element.querySelector('.fl-dropdown-label').innerHTML).toBe('Findologic::Template.pleaseSelect');
    });

    it('shows subcategories when the parent category is selected', () => {
        const facet: Facet = {
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
                    items: [
                        {
                            count: 2,
                            id: '5',
                            image: '',
                            items: [],
                            name: '"Armchairs & Stools"',
                            position: 'item',
                            selected: false
                        },
                        {
                            count: 2,
                            id: '6',
                            image: '',
                            items: [],
                            name: 'Sofas',
                            position: 'item',
                            selected: false
                        }
                    ],
                    name: 'Living Room',
                    position: 'item',
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

        expect(wrapper.element.querySelectorAll(':scope > div.fl-category-dropdown-container.custom-select > ul > li').length).toBe(1);
        expect(wrapper.element.querySelectorAll(':scope > div.fl-category-dropdown-container.custom-select > ul > li ul.subcategories li').length).toBe(2);
    });

    it('does not show subcategories when no parent category is selected', () => {
        const facet: Facet = {
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
                    items: [
                        {
                            count: 2,
                            id: '5',
                            image: '',
                            items: [],
                            name: '"Armchairs & Stools"',
                            position: 'item',
                            selected: false
                        },
                        {
                            count: 2,
                            id: '6',
                            image: '',
                            items: [],
                            name: 'Sofas',
                            position: 'item',
                            selected: false
                        }
                    ],
                    name: 'Living Room',
                    position: 'item',
                    selected: false
                },
                {
                    count: 4,
                    id: '5',
                    image: '',
                    items: [
                        {
                            count: 2,
                            id: '8',
                            image: '',
                            items: [],
                            name: 'Something',
                            position: 'item',
                            selected: false
                        },
                        {
                            count: 2,
                            id: '9',
                            image: '',
                            items: [],
                            name: 'Something else',
                            position: 'item',
                            selected: false
                        }
                    ],
                    name: 'Living Room',
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

        const wrapper = shallowMount(ItemCategoryDropdown, { propsData: { facet }, store, localVue });

        expect(wrapper.element.querySelectorAll(':scope > div.fl-category-dropdown-container.custom-select > ul > li').length).toBe(2);
        expect(wrapper.element.querySelectorAll(':scope > div.fl-category-dropdown-container.custom-select > ul > li ul.subcategories li').length).toBe(0);
    });
});
