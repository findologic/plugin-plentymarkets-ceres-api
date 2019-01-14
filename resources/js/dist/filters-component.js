(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";

var _url = require("./mixins/url");

var _url2 = _interopRequireDefault(_url);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

Vue.component("findologic-filter-list", {
    mixins: [_url2.default],

    delimiters: ["${", "}"],

    props: ["template", "facetData"],

    data: function data() {
        return {
            isActive: false
        };
    },


    computed: Vuex.mapState({
        facets: function facets(state) {
            return state.itemList.facets.sort(function (facetA, facetB) {
                if (facetA.position > facetB.position) {
                    return 1;
                }
                if (facetA.position < facetB.position) {
                    return -1;
                }

                return 0;
            });
        }
    }),

    created: function created() {
        this.$store.commit("setFacets", this.facetData);

        this.$options.template = this.template || "#vue-findologic-filter-list";

        var urlParams = this.getUrlParams(document.location.search);

        var selectedFacets = [];

        if (urlParams.facets) {
            selectedFacets = urlParams.facets.split(",");
        }

        if (urlParams.priceMin || urlParams.priceMax) {
            var priceMin = urlParams.priceMin || "";
            var priceMax = urlParams.priceMax || "";

            this.$store.commit("setPriceFacet", { priceMin: priceMin, priceMax: priceMax });

            selectedFacets.push("price");
        }

        if (selectedFacets.length > 0) {
            this.$store.commit("setSelectedFacetsByIds", selectedFacets);
        }
    },


    methods: {
        toggleOpeningState: function toggleOpeningState() {
            var _this = this;

            window.setTimeout(function () {
                _this.isActive = !_this.isActive;
            }, 300);
        }
    }

});

},{"./mixins/url":8}],2:[function(require,module,exports){
"use strict";

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

var _url = require("./mixins/url");

var _url2 = _interopRequireDefault(_url);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

Vue.component("findologic-item-filter", {
    mixins: [_url2.default],

    delimiters: ["${", "}"],

    props: ["template", "facet"],

    computed: _extends({
        facets: function facets() {
            return this.facet.values.sort(function (facetA, facetB) {
                if (facetA.position > facetB.position) {
                    return 1;
                }
                if (facetA.position < facetB.position) {
                    return -1;
                }

                return 0;
            });
        }
    }, Vuex.mapState({
        selectedFacets: function selectedFacets(state) {
            return state.itemList.selectedFacets;
        },
        isLoading: function isLoading(state) {
            return state.itemList.isLoading;
        }
    })),

    created: function created() {
        this.$options.template = this.template || "#vue-findologic-item-filter";
    },


    methods: {
        updateFacet: function updateFacet(facetValue) {
            this.updateSelectedFilters(this.facet.id, facetValue.name);
        },
        isSelected: function isSelected(facetValue) {
            return this.isValueSelected(this.facet.id, facetValue.name);
        }
    }
});

},{"./mixins/url":8}],3:[function(require,module,exports){
"use strict";

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

var _url = require("./mixins/url");

var _url2 = _interopRequireDefault(_url);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

Vue.component("findologic-item-filter-price", {
    mixins: [_url2.default],

    delimiters: ["${", "}"],

    props: ["template", "facet"],

    data: function data() {
        return {
            priceMin: "",
            priceMax: "",
            currency: App.activeCurrency
        };
    },
    created: function created() {
        this.$options.template = this.template || "#vue-findologic-item-filter-price";

        var values = this.getSelectedFilterValue(this.facet.id);

        this.priceMin = values ? values.min : "";
        this.priceMax = values ? values.max : "";
    },


    computed: _extends({
        isDisabled: function isDisabled() {
            return this.priceMin === "" && this.priceMax === "" || parseInt(this.priceMin) >= parseInt(this.priceMax) || this.isLoading;
        }
    }, Vuex.mapState({
        isLoading: function isLoading(state) {
            return state.itemList.isLoading;
        }
    })),

    methods: {
        selectAll: function selectAll(event) {
            event.target.select();
        },
        triggerFilter: function triggerFilter() {
            if (!this.isDisabled) {
                this.updateSelectedFilters(this.facet.id, { min: this.priceMin, max: this.priceMax });
            }
        }
    }
});

},{"./mixins/url":8}],4:[function(require,module,exports){
"use strict";

var _url = require("./mixins/url");

var _url2 = _interopRequireDefault(_url);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

Vue.component("item-filter-tag-list", {
    mixins: [_url2.default],

    delimiters: ["${", "}"],

    props: ["template"],

    computed: Vuex.mapState({
        tagList: function tagList(state) {
            return undefined.getSelectedFilters();
        }
    }),

    created: function created() {
        this.$options.template = this.template || "#vue-findologic-item-filter-tag-list";
    },


    methods: {
        removeTag: function removeTag(tag) {
            this.$store.dispatch("selectFacet", tag);
        }
    }
});

},{"./mixins/url":8}],5:[function(require,module,exports){
"use strict";

var _url = require("./mixins/url");

var _url2 = _interopRequireDefault(_url);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

Vue.component("item-list-sorting", {
    mixins: [_url2.default],

    delimiters: ["${", "}"],

    props: ["sortingList", "defaultSorting", "template"],

    data: function data() {
        return {
            selectedSorting: {}
        };
    },
    created: function created() {
        this.$options.template = this.template || "#vue-item-list-sorting";
        this.setSelectedValue();
    },


    methods: {
        updateSorting: function updateSorting() {
            this.setUrlParamValue('sorting', this.selectedSorting);
        },


        /**
         * Determine the initial value and set it in the vuex storage.
         */
        setSelectedValue: function setSelectedValue() {
            var urlParams = this.getUrlParams(document.location.search);

            if (urlParams.sorting) {
                this.selectedSorting = urlParams.sorting;
            } else {
                this.selectedSorting = this.defaultSorting;
            }

            this.$store.commit("setItemListSorting", this.selectedSorting);
        }
    }
});

},{"./mixins/url":8}],6:[function(require,module,exports){
"use strict";

var _url = require("./mixins/url");

var _url2 = _interopRequireDefault(_url);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

Vue.component("items-per-page", {
    mixins: [_url2.default],

    delimiters: ["${", "}"],

    props: ["paginationValues", "template"],

    data: function data() {
        return {
            selectedValue: null
        };
    },
    created: function created() {
        this.$options.template = this.template || "#vue-items-per-page";
        this.setSelectedValueByUrl();
    },


    methods: {
        itemsPerPageChanged: function itemsPerPageChanged() {
            this.setUrlParamValue('items', this.selectedValue);
        },
        setSelectedValueByUrl: function setSelectedValueByUrl() {
            var urlParams = this.getUrlParams(document.location.search);
            var defaultItemsPerPage = App.config.pagination.columnsPerPage * App.config.pagination.rowsPerPage[0];

            if (urlParams.items) {
                if (this.paginationValues.includes(parseInt(urlParams.items))) {
                    this.selectedValue = urlParams.items;
                } else {
                    this.selectedValue = defaultItemsPerPage;
                }
            } else {
                this.selectedValue = defaultItemsPerPage;
            }

            this.$store.commit("setItemsPerPage", parseInt(this.selectedValue));
        }
    }
});

},{"./mixins/url":8}],7:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
    value: true
});
var PARAMETER_ATTRIBUTES = 'attrib';
var PARAMETER_PROPERTIES = 'properties';
var PARAMETER_SORT_ORDER = 'order';
var PARAMETER_PAGINATION_ITEMS_PER_PAGE = 'count';
var PARAMETER_PAGINATION_START = 'first';

exports.default = {
    PARAMETER_ATTRIBUTES: PARAMETER_ATTRIBUTES,
    PARAMETER_PROPERTIES: PARAMETER_PROPERTIES,
    PARAMETER_SORT_ORDER: PARAMETER_SORT_ORDER,
    PARAMETER_PAGINATION_ITEMS_PER_PAGE: PARAMETER_PAGINATION_ITEMS_PER_PAGE,
    PARAMETER_PAGINATION_START: PARAMETER_PAGINATION_START
};

},{}],8:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
    value: true
});

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _constants = require("../constants");

var _constants2 = _interopRequireDefault(_constants);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

exports.default = {
    methods: {
        getUrlParams: function getUrlParams(urlParams) {
            if (urlParams) {
                var tokens;
                var params = {};
                var regex = /[?&]?([^=]+)=([^&]*)/g;

                urlParams = urlParams.split("+").join(" ");

                // eslint-disable-next-line
                while (tokens = regex.exec(urlParams)) {
                    params[decodeURIComponent(tokens[1])] = decodeURIComponent(tokens[2]);
                }

                return params;
            }

            return {};
        },
        getSearchParams: function getSearchParams() {
            var queryString = document.location.search;
            var requestParameters = {};

            /*
             * Remove any leading ? as it is not part of the query string but will be passed due to the way we use
             * parseQueryString
             */
            if (typeof queryString !== 'undefined') {
                queryString = queryString.replace(/^\?/, "");
            } else {
                queryString = '';
            }

            var strArr = String(queryString).replace(/^&/, '').replace(/&$/, '').split('&'),
                sal = strArr.length,
                i,
                j,
                ct,
                p,
                lastObj,
                obj,
                lastIter,
                undef,
                chr,
                tmp,
                key,
                value,
                postLeftBracketPos,
                keys,
                keysLen,
                fixStr = function fixStr(queryString) {
                return decodeURIComponent(queryString.replace(/\+/g, '%20'));
            };

            for (i = 0; i < sal; i++) {
                tmp = strArr[i].split('=');
                key = fixStr(tmp[0]);
                value = tmp.length < 2 ? '' : fixStr(tmp[1]);

                while (key.charAt(0) === ' ') {
                    key = key.slice(1);
                }
                if (key.indexOf('\x00') > -1) {
                    key = key.slice(0, key.indexOf('\x00'));
                }
                if (key && key.charAt(0) !== '[') {
                    keys = [];
                    postLeftBracketPos = 0;
                    for (j = 0; j < key.length; j++) {
                        if (key.charAt(j) === '[' && !postLeftBracketPos) {
                            postLeftBracketPos = j + 1;
                        } else if (key.charAt(j) === ']') {
                            if (postLeftBracketPos) {
                                if (!keys.length) {
                                    keys.push(key.slice(0, postLeftBracketPos - 1));
                                }
                                keys.push(key.substr(postLeftBracketPos, j - postLeftBracketPos));
                                postLeftBracketPos = 0;
                                if (key.charAt(j + 1) !== '[') {
                                    break;
                                }
                            }
                        }
                    }
                    if (!keys.length) {
                        keys = [key];
                    }
                    for (j = 0; j < keys[0].length; j++) {
                        chr = keys[0].charAt(j);
                        if (chr === ' ' || chr === '.' || chr === '[') {
                            keys[0] = keys[0].substr(0, j) + '_' + keys[0].substr(j + 1);
                        }
                        if (chr === '[') {
                            break;
                        }
                    }

                    obj = requestParameters;
                    for (j = 0, keysLen = keys.length; j < keysLen; j++) {
                        key = keys[j].replace(/^['"]/, '').replace(/['"]$/, '');
                        lastIter = j !== keys.length - 1;
                        lastObj = obj;
                        if (key !== '' && key !== ' ' || j === 0) {
                            if (obj[key] === undef) {
                                obj[key] = {};
                            }
                            obj = obj[key];
                        } else {
                            // To insert new dimension
                            ct = -1;
                            for (p in obj) {
                                if (obj.hasOwnProperty(p)) {
                                    if (+p > ct && p.match(/^\d+$/g)) {
                                        ct = +p;
                                    }
                                }
                            }
                            key = ct + 1;
                        }
                    }
                    lastObj[key] = value;
                }
            }

            return requestParameters;
        },
        updateSelectedFilters: function updateSelectedFilters(facetId, facetValue) {
            var params = this.getSearchParams();

            if (!(_constants2.default.PARAMETER_ATTRIBUTES in params)) {
                params[_constants2.default.PARAMETER_ATTRIBUTES] = {};
            }

            var attributes = params[_constants2.default.PARAMETER_ATTRIBUTES];

            if (this.facet.id === 'price') {
                attributes[facetId] = {
                    min: facetValue.min,
                    max: facetValue.max
                };
            } else if (this.facet.select === 'single') {
                if (facetId in attributes) {
                    if (attributes[facetId] === facetValue) {
                        delete attributes[facetId];
                    } else {
                        attributes[facetId] = facetValue;
                    }
                } else {
                    attributes[facetId] = facetValue;
                }
            } else {
                if (!(facetId in attributes)) {
                    attributes[facetId] = [facetValue];
                } else {
                    var valueId = this.getKeyByValue(attributes[facetId], facetValue);

                    if (valueId === -1) {
                        var index = Object.keys(attributes[facetId]).length;
                        attributes[facetId][index] = facetValue;
                    } else {
                        delete attributes[facetId][valueId];
                    }
                }
            }

            params[_constants2.default.PARAMETER_ATTRIBUTES] = attributes;

            document.location.search = '?' + $.param(params);
        },
        removeSelectedFilter: function removeSelectedFilter(facetId, facetValue) {
            var params = this.getSearchParams();
            var attributes = params[_constants2.default.PARAMETER_ATTRIBUTES];

            if (_typeof(attributes[filter]) !== 'object' || facetId === 'price') {
                delete attributes[facetId];
            } else {
                var values = attributes[filter];
                for (var value in values) {
                    if (values[value] === facetValue) {
                        delete attributes[facetId][value];
                    }
                }
            }

            params[_constants2.default.PARAMETER_ATTRIBUTES] = attributes;

            document.location.search = '?' + $.param(params);
        },
        isValueSelected: function isValueSelected(facetId, facetValue) {
            var params = this.getSearchParams();

            if (!(_constants2.default.PARAMETER_ATTRIBUTES in params)) {
                return false;
            }

            var attributes = params[_constants2.default.PARAMETER_ATTRIBUTES];

            if (!(facetId in attributes)) {
                return false;
            }

            if (this.facet.select === 'single' && attributes[facetId] === facetValue) {
                return true;
            }

            if (this.getKeyByValue(attributes[facetId], facetValue) !== -1) {
                return true;
            }

            return false;
        },
        getSelectedFilters: function getSelectedFilters() {
            var selectedFilters = [];
            var params = this.getSearchParams();

            if (!(_constants2.default.PARAMETER_ATTRIBUTES in params)) {
                return selectedFilters;
            }

            var attributes = params[_constants2.default.PARAMETER_ATTRIBUTES];

            for (var filter in attributes) {
                if (filter === 'price') {
                    selectedFilters.push({
                        id: 'price',
                        name: attributes[filter].min + ' - ' + attributes[filter].max
                    });

                    continue;
                }

                if (_typeof(attributes[filter]) === 'object') {
                    var values = attributes[filter];
                    for (var value in values) {
                        selectedFilters.push({
                            id: filter,
                            name: values[value]
                        });
                    }
                    continue;
                }

                selectedFilters.push({
                    id: filter,
                    name: attributes[filter]
                });
            }

            return selectedFilters;
        },
        getSelectedFilterValue: function getSelectedFilterValue(facetId) {
            var params = this.getSearchParams();

            if (!(_constants2.default.PARAMETER_ATTRIBUTES in params)) {
                return null;
            }

            var attributes = params[_constants2.default.PARAMETER_ATTRIBUTES];

            if (!(facetId in attributes)) {
                return null;
            }

            return attributes[facetId];
        },
        getUrlParamValue: function getUrlParamValue(key) {
            var params = this.getSearchParams();

            if (!(key in params)) {
                return null;
            }

            return params[key];
        },
        setUrlParamValue: function setUrlParamValue(key, value) {
            var params = this.getSearchParams();

            params[key] = value;

            document.location.search = '?' + $.param(params);
        },
        getKeyByValue: function getKeyByValue(object, value) {
            for (var prop in object) {
                if (object.hasOwnProperty(prop)) {
                    if (object[prop] === value) return prop;
                }
            }

            return -1;
        }
    }
};

},{"../constants":7}]},{},[1,2,3,4,5,6])


//# sourceMappingURL=filters-component.js.map
