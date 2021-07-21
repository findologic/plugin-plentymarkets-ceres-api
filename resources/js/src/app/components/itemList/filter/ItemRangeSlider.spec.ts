import Vuex from 'vuex';
import {shallowMount, createLocalVue, mount} from '@vue/test-utils';
import { Facet, State } from '../../../shared/interfaces';
import { Store } from 'vuex';
import ItemRangeSlider from './ItemRangeSlider.vue';
import VueCompositionAPI from '@vue/composition-api';
import $ from 'jquery'
import UrlBuilder from "../../../shared/UrlBuilder";

const localVue = createLocalVue();

localVue.use(Vuex);
localVue.use(VueCompositionAPI);

window.ceresTranslate = key => key;

describe('ItemRangeSlider', () => {

    let store: Store<State>;
    global.$ = $

    beforeEach(() => {
        store = new Vuex.Store({});
    });

    it('Redirects to page with filter params applied after the submit button is clicked', async () => {
        const facet: Facet = {
            cssClass: '',
            findologicFilterType: 'range-slider',
            id: 'price',
            isMain: true,
            itemCount: 0,
            maxValue: 149,
            minValue: 59,
            name: 'Price',
            noAvailableFiltersText: '',
            select: 'single',
            step: 0.1,
            type: '',
            unit: '£',
            values: [
                {
                    count: 9,
                    id: '20',
                    image: '',
                    items: [],
                    name:'59 - 99',
                    position: 'item',
                    selected: false
                },
                {
                    count: 1,
                    id: '21',
                    image: '',
                    items: [],
                    name: '119 - 149',
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
        UrlBuilder.updateSelectedFilters = jest.fn();

        const wrapper = shallowMount(ItemRangeSlider, { propsData: { facet }, store, localVue });

        const submitButton = wrapper.find('button');
        await submitButton.trigger('click')
        expect(UrlBuilder.updateSelectedFilters).toHaveBeenNthCalledWith(1, facet, 'price', {'max': 149, 'min': 59});

        wrapper.setData({valueFrom: 100, valueTo: 140.55});
        await submitButton.trigger('click')
        expect(UrlBuilder.updateSelectedFilters).toHaveBeenNthCalledWith(2, facet, 'price', {'max': 140.55, 'min': 100});
    });

    it('Disables the filter submit button if fields contain incorrect values', async () => {
        const facet: Facet = {
            cssClass: '',
            findologicFilterType: 'range-slider',
            id: 'price',
            isMain: true,
            itemCount: 0,
            maxValue: 149,
            minValue: 59,
            name: 'Price',
            noAvailableFiltersText: '',
            select: 'single',
            step: 0.1,
            type: '',
            unit: '£',
            values: [
                {
                    count: 9,
                    id: '20',
                    image: '',
                    items: [],
                    name:'59 - 99',
                    position: 'item',
                    selected: false
                },
                {
                    count: 1,
                    id: '21',
                    image: '',
                    items: [],
                    name: '119 - 149',
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
        UrlBuilder.updateSelectedFilters = jest.fn();

        const wrapper = shallowMount(ItemRangeSlider, { propsData: { facet }, store, localVue });
        const submitButton = wrapper.find('button');

        await wrapper.setData({valueFrom: '', valueTo: ''});
        expect(submitButton.classes()).toContain('disabled');
        await submitButton.trigger('click')

        await wrapper.setData({valueFrom: 'notNumber', valueTo: 'someTextInsteadOfANumber'});
        expect(submitButton.classes()).toContain('disabled');
        await submitButton.trigger('click')

        expect(UrlBuilder.updateSelectedFilters).not.toHaveBeenCalled();
    });
});
