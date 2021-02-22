import { Store } from 'vuex'

declare module '@vue/runtime-core' {
    interface State {
        count: number;
    }

    interface CustomProperties {
        $store: Store<State>;
    }
}
