import { shallowMount, createLocalVue  } from '@vue/test-utils';
import Vuex from 'vuex';
import { MUTATIONS } from "./mocks/store";
import { FACET } from "./mocks/mocks";
import ItemRangeSlider from "./components/ItemRangeSlider.vue";

let wrapper;
const localVue = createLocalVue();
localVue.use(Vuex);

localVue.directive('tooltip', {});
localVue.directive('waiting-animation', {});

let store = new Vuex.Store({
    mutations: MUTATIONS,
});

describe('ItemRangeSlider.vue', () => {
    beforeEach(() => {
        wrapper = shallowMount(ItemRangeSlider, {
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
