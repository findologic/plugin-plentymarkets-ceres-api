import Url from '../../../mixins/url';
import baseDropdown from '../../../mixins/baseDropdown';
import Vue from "vue";

Vue.component("item-dropdown", {
    mixins: [Url, baseDropdown]
});
