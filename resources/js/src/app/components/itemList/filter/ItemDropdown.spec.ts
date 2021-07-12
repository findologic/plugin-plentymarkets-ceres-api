import Vuex from 'vuex';
import { shallowMount, createLocalVue } from '@vue/test-utils';
import { Facet } from '../../../shared/interfaces';
import ItemDropdown from './ItemDropdown.vue';
import VueCompositionAPI from '@vue/composition-api';

const localVue = createLocalVue();

localVue.use(Vuex);
localVue.use(VueCompositionAPI);

window.ceresTranslate = key => key;

describe('ItemDropdown', () => {

    let store: Store<State>;

    beforeEach(() => {
        store = new Vuex.Store({});
    });

    it('does not show a dropdown if the fixed value count is greater than the available filter values', () => {
        const facet: Facet = {
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

        store.state.itemList = {
            isLoading: false,
            selectedFacets: [],
            facets: [facet]
        };

        const wrapper = shallowMount(ItemDropdown, { propsData: { facet }, store, localVue });

        expect(wrapper.element.querySelectorAll('.fl-dropdown-container').length).toBe(0);
    });

    it('shows one filter value inside a dropdown if there are two options and the fixed item count is set to 1', () => {
        const facet: Facet = {
            cssClass: '',
            findologicFilterType: 'select',
            id: 'test',
            isMain: false,
            itemCount: 1,
            name: 'Test',
            noAvailableFiltersText: '',
            select: 'multiple',
            type: '',
            values: [
                {
                    count: 9,
                    id: '20',
                    items: [],
                    name: '22220',
                    position: 'item',
                    selected: false
                },
                {
                    count: 1,
                    id: '21',
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

        const wrapper = shallowMount(ItemDropdown, { propsData: { facet }, store, localVue });

        expect(wrapper.element.querySelectorAll(':scope > *').length).toBe(2);
        expect(wrapper.element.querySelectorAll(':scope > div.form-check').length).toBe(1);
        expect(wrapper.element.querySelectorAll(':scope > div.fl-dropdown-container.custom-select ul li').length).toBe(1);
    });


    it('shows one filter value inside a dropdown if there are two options and the fixed item count is set to 1', () => {
        const facet: Facet = {
            cssClass: '',
            findologicFilterType: 'select',
            id: 'test',
            isMain: false,
            itemCount: 0,
            name: 'Test',
            noAvailableFiltersText: '',
            select: 'multiple',
            type: '',
            values: [
                {
                    count: 9,
                    id: '20',
                    items: [],
                    name: '22220',
                    position: 'item',
                    selected: false
                },
                {
                    count: 1,
                    id: '21',
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

        const wrapper = shallowMount(ItemDropdown, { propsData: { facet }, store, localVue });

        expect(wrapper.element.querySelectorAll(':scope > *').length).toBe(1);
        expect(wrapper.element.querySelectorAll(':scope > div.fl-dropdown-container.custom-select ul li').length).toBe(2);
        expect(wrapper.element.querySelector('.fl-dropdown-label').innerHTML).toBe('Findologic::Template.pleaseSelect');
    });
});
