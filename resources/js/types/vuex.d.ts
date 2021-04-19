import { Store } from 'vuex'
import { ItemListData } from '../src/app/shared/interfaces';
import FacetService from '../src/app/services/facet.service';

declare module '@vue/runtime-core' {
    interface State {
        itemList: ItemListData;
    }

    interface CustomProperties {
        $store: Store<State>;
        $facetService: FacetService;
    }
}
