import Vue from 'vue';
import { shallowMount } from '@vue/test-utils';
import ItemSearch from "./components/ItemSearch.vue";

describe('ItemSearch.vue', () => {
    const wrapper = shallowMount(ItemSearch, {
        mocks: {
            $store: {
                commit: (store, parameters) => {}
            }
        }
    });
    // checks ItemSearch is a component.
    it('Is Vue Component', () => {
        expect(wrapper.isVueInstance()).toBeTruthy();
    });
});