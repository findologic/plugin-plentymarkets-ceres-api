import { Store } from 'vuex'
import { ItemListData } from '../src/app/interfaces';

declare module '@vue/runtime-core' {
    interface State {
        itemList: ItemListData;
    }

    interface CustomProperties {
        $store: Store<State>;
    }
}
