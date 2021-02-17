// Is automatically injected by the Ceres plugin.
import { Store } from 'vuex';

declare type VueDeclaration = import('vue').default;
declare class Vue implements VueDeclaration{
    $store: Store<any>
}

// declare module 'vue/types/vue' {
//     import { Store } from 'vuex';
//
//     interface Vue {
//         $store: Store<any>
//     }
// }
