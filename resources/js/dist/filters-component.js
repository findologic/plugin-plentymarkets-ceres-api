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

},{"./mixins/url":5}],2:[function(require,module,exports){
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
            this.updateSelectedFilters(this.facet.id, facetValue);
        },
        isSelected: function isSelected(facetValueId) {
            return this.selectedFacets.findIndex(function (selectedFacet) {
                return selectedFacet.id === facetValueId;
            }) > -1;
        }
    }
});

},{"./mixins/url":5}],3:[function(require,module,exports){
"use strict";

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

var _url = require("./mixins/url");

var _url2 = _interopRequireDefault(_url);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

Vue.component("findologic-item-filter-price", {
    mixins: [_url2.default],

    delimiters: ["${", "}"],

    props: {
        template: {
            type: String,
            default: "#vue-item-filter-price"
        }
    },

    data: function data() {
        return {
            priceMin: "",
            priceMax: "",
            currency: App.activeCurrency
        };
    },
    created: function created() {
        this.$options.template = this.template || "#vue-findologic-item-filter-price";

        var urlParams = this.getUrlParams(document.location.search);

        this.priceMin = urlParams.priceMin || "";
        this.priceMax = urlParams.priceMax || "";
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
                this.updateSelectedFilters(this.facet.id, { priceMin: this.priceMin, priceMax: this.priceMax });
            }
        }
    }
});

},{"./mixins/url":5}],4:[function(require,module,exports){
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

},{}],5:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
    value: true
});

var _constants = require('../constants');

var _constants2 = _interopRequireDefault(_constants);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

exports.default = {
    methods: {
        getUrlParams: function getUrlParams(queryString) {
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
            var params = this.getUrlParams(document.location.search);
            var value = facetValue.name;

            console.log('url params');
            console.log(params);

            if (!(_constants2.default.PARAMETER_ATTRIBUTES in params)) {
                params[_constants2.default.PARAMETER_ATTRIBUTES] = {};
            }

            var attributes = params[_constants2.default.PARAMETER_ATTRIBUTES];

            if (this.facet.select === 'single') {
                if (facetId in attributes) {
                    if (attributes[facetId] === value) {
                        delete attributes[facetId];
                    } else {
                        attributes[facetId] = value;
                    }
                } else {
                    attributes[facetId] = value;
                }
            } else {
                if (!(facetId in attributes)) {
                    attributes[facetId] = [value];
                } else if ($.inArray(value, attributes[facetId]) !== -1) {
                    attributes[facetId].push(value);
                } else {
                    attributes[facetId] = attributes[facetId].filter(function (selectedValue) {
                        return selectedValue !== value;
                    });
                }
            }

            params[_constants2.default.PARAMETER_ATTRIBUTES] = attributes;

            console.log('updated url params');
            console.log(params);
            console.log($.param(params));
            document.location.search = '?' + $.param(params);
        }
    }
};

},{"../constants":4}]},{},[1,2,3])


//# sourceMappingURL=filters-component.js.map
