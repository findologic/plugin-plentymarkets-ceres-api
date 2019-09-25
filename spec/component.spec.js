import { shallowMount , config} from '@vue/test-utils';
import ItemSearch from "./components/ItemSearch";

//App mock is in global jetzt config
//import {App} from "./mocks/mocks";

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