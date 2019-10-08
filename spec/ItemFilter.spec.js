import { shallowMount, createLocalVue  } from '@vue/test-utils';
import Vuex from 'vuex';
import { MUTATIONS } from "./mocks/store";
import { FACET } from "./mocks/mocks";
import ItemFilter from "./components/ItemFilter.vue";

let wrapper;
const localVue = createLocalVue();
localVue.use(Vuex);

let store = new Vuex.Store({
    mutations: MUTATIONS,
});

describe('ItemFilter.vue', () => {
    beforeEach(() => {
        wrapper = shallowMount(ItemFilter, {
            propsData: {facet: FACET},
            mocks: {},
            stubs: {},
            methods: {},
            store,
            localVue,
        });
    });

    afterEach(() => {
        wrapper.destroy();
    });
    // checks ItemSearch is a component.
    it('Is Vue Component', () => {
        expect(wrapper.isVueInstance()).toBeTruthy();
    });
});
