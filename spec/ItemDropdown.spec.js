import { shallowMount, createLocalVue  } from '@vue/test-utils';
import Vuex from 'vuex';
import { MUTATIONS } from "./mocks/store";
import { FACET } from "./mocks/mocks";
import ItemDropdown from "./components/ItemDropdown.vue";

let wrapper;
const localVue = createLocalVue();
localVue.use(Vuex);
localVue.Vuex = {};

let store = new Vuex.Store({
    mutations: MUTATIONS,
});

describe('ItemDropdown.vue', () => {
    beforeEach(() => {
        wrapper = shallowMount(ItemDropdown, {
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
