(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";

var _url = require("../../mixins/url");

var _url2 = _interopRequireDefault(_url);

var _constants = require("../../constants");

var _constants2 = _interopRequireDefault(_constants);

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
            this.setUrlParamValues([{
                key: _constants2.default.PARAMETER_SORTING,
                value: this.selectedSorting
            }, {
                key: _constants2.default.PARAMETER_PAGE,
                value: 1
            }]);
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

},{"../../constants":14,"../../mixins/url":17}],2:[function(require,module,exports){
"use strict";

var _url = require("../../mixins/url");

var _url2 = _interopRequireDefault(_url);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

Vue.component("item-search", {
    mixins: [_url2.default],

    props: {
        template: {
            type: String,
            default: "#vue-item-search"
        },
        showItemImages: {
            type: Boolean,
            default: false
        },
        forwardToSingleItem: {
            type: Boolean,
            default: App.config.search.forwardToSingleItem
        }
    },

    data: function data() {
        return {
            promiseCount: 0,
            autocompleteResult: [],
            selectedAutocompleteIndex: -1,
            isSearchFocused: false
        };
    },


    computed: {
        selectedAutocompleteItem: function selectedAutocompleteItem() {
            return null;
        }
    },

    created: function created() {
        this.$options.template = this.template;
    },
    mounted: function mounted() {
        var _this = this;

        this.$nextTick(function () {
            var urlParams = _this.getUrlParams(document.location.search);

            _this.$store.commit("setItemListSearchString", urlParams.query);

            var rawQuery = urlParams.query ? urlParams.query : '';
            // Manually regex out all "+" signs as decodeURIComponent does not take care of that.
            // If we wouldn't replace them with spaces, "+" signs would be displayed in the search field.
            _this.$refs.searchInput.value = decodeURIComponent(rawQuery.replace(/\+/g, ' '));
        });
    },


    methods: {
        search: function search() {
            $('#searchBox').collapse('hide');

            var searchBaseURL = '/search?query=';

            if (App.defaultLanguage !== App.language) {
                searchBaseURL = "/" + App.language + "/search?query=";
            }

            window.open(searchBaseURL + this.$refs.searchInput.value, '_self', false);
        },
        autocomplete: function autocomplete(searchString) {},
        selectAutocompleteItem: function selectAutocompleteItem(item) {},
        keyup: function keyup() {},
        keydown: function keydown() {},


        // hide autocomplete after 100ms to make clicking on it possible
        setIsSearchFocused: function setIsSearchFocused(value) {
            var _this2 = this;

            setTimeout(function () {
                _this2.isSearchFocused = !!value;
            }, 100);
        }
    }
});

},{"../../mixins/url":17}],3:[function(require,module,exports){
"use strict";

var _url = require("../../mixins/url");

var _url2 = _interopRequireDefault(_url);

var _constants = require("../../constants");

var _constants2 = _interopRequireDefault(_constants);

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
            this.setUrlParamValues([{
                key: _constants2.default.PARAMETER_ITEMS,
                value: this.selectedValue
            }, {
                key: _constants2.default.PARAMETER_PAGE,
                value: 1
            }]);
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

},{"../../constants":14,"../../mixins/url":17}],4:[function(require,module,exports){
"use strict";

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

var _url = require("../../mixins/url");

var _url2 = _interopRequireDefault(_url);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var options = {
    mixins: [_url2.default],

    delimiters: ["${", "}"],

    props: ["template"],

    data: function data() {
        return {
            lastPageMax: 0
        };
    },


    computed: _extends({
        pageMax: function pageMax() {
            if (this.isLoading) {
                return this.lastPageMax;
            }

            var pageMax = this.totalItems / parseInt(this.itemsPerPage);

            if (this.totalItems % parseInt(this.itemsPerPage) > 0) {
                pageMax += 1;
            }

            this.lastPageMax = parseInt(pageMax) || 1;

            return parseInt(pageMax) || 1;
        }
    }, Vuex.mapState({
        page: function page(state) {
            return state.itemList.page || 1;
        },
        isLoading: function isLoading(state) {
            return state.itemList.isLoading;
        },
        itemsPerPage: function itemsPerPage(state) {
            return state.itemList.itemsPerPage;
        },
        totalItems: function totalItems(state) {
            return state.itemList.totalItems;
        }
    })),

    created: function created() {
        this.$options.template = this.template;

        var urlParams = this.getUrlParams(document.location.search);
        var page = urlParams.page || 1;

        this.$store.commit("setItemListPage", parseInt(page));
    },


    methods: {
        setPage: function setPage(page) {
            this.setUrlParamValue('page', page);
        }
    }
};

Vue.component('pagination', options);
Vue.component('custom-pagination', options);

},{"../../mixins/url":17}],5:[function(require,module,exports){
"use strict";

var _url = require("../../../mixins/url");

var _url2 = _interopRequireDefault(_url);

var _baseDropdown = require("../../../mixins/baseDropdown");

var _baseDropdown2 = _interopRequireDefault(_baseDropdown);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

Vue.component("item-category-dropdown", {
    mixins: [_url2.default, _baseDropdown2.default],

    computed: {
        dropdownLabel: function dropdownLabel() {
            var selectedFilters = this.getSelectedFilters();
            var label = null;

            for (var i = 0; i < selectedFilters.length; i++) {
                var facet = selectedFilters[i];

                if (facet.id === this.facet.id) {
                    label = facet.name;
                    break;
                }
            }

            return label;
        }
    },

    methods: {
        getSubCategoryName: function getSubCategoryName(parentCategory, subCategory) {
            return parentCategory.name + '_' + subCategory.name;
        }
    }
});

},{"../../../mixins/baseDropdown":16,"../../../mixins/url":17}],6:[function(require,module,exports){
"use strict";

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

var _url = require("../../../mixins/url");

var _url2 = _interopRequireDefault(_url);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

Vue.component("item-color-tiles", {
    mixins: [_url2.default],

    props: ["template", "facet", "fallbackImage"],

    created: function created() {
        this.$options.template = this.template || "#vue-item-color-tiles";
    },


    computed: _extends({}, Vuex.mapState({
        isLoading: function isLoading(state) {
            return state.itemList.isLoading;
        }
    })),

    mounted: function mounted() {
        this.$nextTick(function () {
            SVGInjector($('img.fl-svg'));
        });
    },


    methods: {
        isSelected: function isSelected(facetValueName) {
            var facetValue = this.facet.values.filter(function (value) {
                return value.name === facetValueName;
            });

            return facetValue.length && this.isValueSelected(this.facet.id, facetValue[0].name);
        },


        tileClicked: function tileClicked(value) {
            this.updateSelectedFilters(this.facet.id, value);
        },

        handleImageError: function handleImageError(event, colorValue) {
            if (!colorValue.hexValue) {
                event.target.src = this.fallbackImage;
            } else {
                event.target.remove();
            }
        }
    }
});

},{"../../../mixins/url":17}],7:[function(require,module,exports){
'use strict';

var _url = require('../../../mixins/url');

var _url2 = _interopRequireDefault(_url);

var _baseDropdown = require('../../../mixins/baseDropdown');

var _baseDropdown2 = _interopRequireDefault(_baseDropdown);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

Vue.component("item-dropdown", {
    mixins: [_url2.default, _baseDropdown2.default]
});

},{"../../../mixins/baseDropdown":16,"../../../mixins/url":17}],8:[function(require,module,exports){
"use strict";

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

var _url = require("../../../mixins/url");

var _url2 = _interopRequireDefault(_url);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

Vue.component("findologic-item-filter", {
    mixins: [_url2.default],

    delimiters: ["${", "}"],

    props: ["template", "facet"],

    data: function data() {
        return {
            facetType: null
        };
    },


    computed: _extends({
        facets: function facets() {
            return this.facet.values.sort(function (facetA, facetB) {
                if (facetA.position > facetB.position) {
                    return 1;
                } else if (facetA.position < facetB.position) {
                    return -1;
                } else {
                    return 0;
                }
            });
        },
        isSelected: function isSelected() {
            var _this = this;

            return typeof this.getSelectedFilters().find(function (element) {
                return element.id == _this.facet.id;
            }) !== 'undefined';
        }
    }, Vuex.mapState({
        selectedFacets: function selectedFacets(state) {
            return state.itemList.selectedFacets;
        },
        isLoading: function isLoading(state) {
            return state.itemList.isLoading;
        }
    }), {
        selectedValuesCount: function selectedValuesCount() {
            var selectedFacets = this.facet.values.filter(function (value) {
                return value.selected;
            });

            return selectedFacets.length;
        }
    }),

    created: function created() {
        this.$options.template = this.template || "#vue-item-filter";
        this.facetType = typeof this.facet.findologicFilterType !== 'undefined' ? this.facet.findologicFilterType : this.facet.type;
    },


    methods: {
        updateFacet: function updateFacet(facetValue) {
            this.updateSelectedFilters(this.facet.id, facetValue.name);
        },
        getSubCategoryValue: function getSubCategoryValue(parentCategory, subCategory) {
            return {
                id: subCategory.id,
                name: parentCategory.name + '_' + subCategory.name
            };
        }
    }
});

},{"../../../mixins/url":17}],9:[function(require,module,exports){
"use strict";

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

var _url = require("../../../mixins/url");

var _url2 = _interopRequireDefault(_url);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

Vue.component("item-filter-image", {
    mixins: [_url2.default],

    props: ["template", "facet", "fallbackImage"],

    created: function created() {
        this.$options.template = this.template || "#vue-findologic-item-filter-image";
    },


    computed: _extends({}, Vuex.mapState({
        isLoading: function isLoading(state) {
            return state.itemList.isLoading;
        }
    })),

    methods: {
        updateFacet: function updateFacet(facetValue) {
            this.updateSelectedFilters(this.facet.id, facetValue.name);
        },
        handleImageError: function handleImageError(event) {
            event.target.src = this.fallbackImage;
        }
    }
});

},{"../../../mixins/url":17}],10:[function(require,module,exports){
"use strict";

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

Vue.component("findologic-item-filter-list", {
    props: {
        facetData: {
            type: Array,
            default: function _default() {
                return [];
            }
        },
        allowedFacetsTypes: {
            type: Array,
            default: function _default() {
                return [];
            }
        },
        showSelectedFiltersCount: {
            type: Boolean,
            default: false
        }
    },

    computed: _extends({}, Vuex.mapState({
        facets: function facets(state) {
            var _this = this;

            if (!this.allowedFacetsTypes.length) {
                return state.itemList.facets;
            }

            return state.itemList.facets.filter(function (facet) {
                return _this.allowedFacetsTypes.includes(facet.id) || _this.allowedFacetsTypes.includes(facet.type);
            });
        },

        isLoading: function isLoading(state) {
            return state.itemList.isLoading;
        },
        selectedFacets: function selectedFacets(state) {
            return state.itemList.selectedFacets;
        }
    })),

    created: function created() {
        this.$store.commit("addFacets", this.facetData);
    }
});

},{}],11:[function(require,module,exports){
"use strict";

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

var _url = require("../../../mixins/url");

var _url2 = _interopRequireDefault(_url);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

Vue.component("item-filter-price", {
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
        this.$options.template = this.template || "#vue-item-filter-price";

        var values = this.getSelectedFilterValue(this.facet.id);

        this.priceMin = values ? values.min : "";
        this.priceMax = values ? values.max : "";
    },


    computed: _extends({
        isDisabled: function isDisabled() {
            return this.priceMin === "" && this.priceMax === "" || parseFloat(this.priceMin) > parseFloat(this.priceMax) || this.isLoading;
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
                var facetValue = {
                    min: this.priceMin,
                    max: this.priceMax ? this.priceMax : Number.MAX_SAFE_INTEGER
                };

                this.updateSelectedFilters(this.facet.id, facetValue);
            }
        }
    }
});

},{"../../../mixins/url":17}],12:[function(require,module,exports){
"use strict";

var _url = require("../../../mixins/url");

var _url2 = _interopRequireDefault(_url);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

Vue.component("item-filter-tag-list", {
    mixins: [_url2.default],

    delimiters: ["${", "}"],

    props: {
        template: {
            type: String,
            default: "#vue-item-filter-tag-list"
        },
        marginClasses: {
            type: String,
            default: null
        },
        marginInlineStyles: {
            type: String,
            default: null
        }
    },

    computed: {
        tagList: function tagList() {
            return this.getSelectedFilters();
        },
        facetInfo: function facetInfo() {
            return this.getFacetIdInfoMap();
        }
    },

    created: function created() {
        this.$options.template = this.template || "#vue-item-filter-tag-list";
    },


    methods: {
        removeTag: function removeTag(tag) {
            this.removeSelectedFilter(tag.id, tag.name);
        },
        resetAllTags: function resetAllTags() {
            this.removeAllAttribsAndRefresh();
        }
    }
});

},{"../../../mixins/url":17}],13:[function(require,module,exports){
"use strict";

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

var _url = require("../../../mixins/url");

var _url2 = _interopRequireDefault(_url);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

Vue.component("item-range-slider", {
    mixins: [_url2.default],

    props: ["template", "facet"],

    data: function data() {
        return {
            valueFrom: "",
            valueTo: ""
        };
    },
    created: function created() {
        var self = this;

        this.$options.template = this.template || "#vue-item-range-slider";

        var values = this.getSelectedFilterValue(this.facet.id);

        this.valueFrom = values ? values.min : this.facet.minValue;
        this.valueTo = values ? values.max : this.facet.maxValue;

        $(document).ready(function () {
            var element = self.$el.querySelector('#' + self.sanitizedFacetId);

            var slider = window.noUiSlider.create(element, {
                step: self.facet.step,
                start: [self.valueFrom, self.valueTo],
                connect: true,
                range: {
                    'min': self.facet.minValue,
                    'max': self.facet.maxValue
                }
            });

            slider.on('update', function (ui) {
                self.valueFrom = ui[0];
                self.valueTo = ui[1];
            });
        });
    },


    computed: _extends({
        sanitizedFacetId: function sanitizedFacetId() {
            return 'fl-range-slider-' + this.facet.id.replace(/\W/g, '-').replace(/-+/, '-').replace(/-$/, '');
        },
        isDisabled: function isDisabled() {
            return this.valueFrom === '' && this.valueTo === '' || parseFloat(this.valueFrom) > parseFloat(this.valueTo) || isNaN(this.valueFrom) || isNaN(this.valueTo) || this.valueFrom === '' || this.valueTo === '' || this.isLoading;
        }
    }, Vuex.mapState({
        isLoading: function isLoading(state) {
            return state.itemList.isLoading;
        }
    })),

    watch: {
        valueFrom: function valueFrom(value) {
            this.valueFrom = this.fixDecimalSeparator(value);
            this.setCustomValidationMessage();
        },
        valueTo: function valueTo(value) {
            this.valueTo = this.fixDecimalSeparator(value);
            this.setCustomValidationMessage();
        }
    },

    methods: {
        triggerFilter: function triggerFilter() {
            if (!this.isDisabled) {
                var facetValue = {
                    min: this.valueFrom,
                    max: this.valueTo ? this.valueTo : Number.MAX_SAFE_INTEGER
                };

                this.updateSelectedFilters(this.facet.id, facetValue);
            }
        },
        fixDecimalSeparator: function fixDecimalSeparator(value) {
            if (typeof value === 'number') {
                value = value.toString();
            }

            if (value.includes('.')) {
                value = value.replace(',', '');
            } else {
                value = value.replace(',', '.');
            }

            return value;
        },
        setCustomValidationMessage: function setCustomValidationMessage() {
            this.$el.querySelectorAll('input.fl-range-input[data-custom-validation-message]').forEach(function (input) {
                // Must be reset before the validity check as existence of custom validity counts as a validation error.
                input.setCustomValidity('');

                if (!input.checkValidity()) {
                    input.setCustomValidity(input.dataset.customValidationMessage);
                }
            });
        }
    }
});

},{"../../../mixins/url":17}],14:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
    value: true
});
var PARAMETER_ATTRIBUTES = 'attrib';
var PARAMETER_PAGE = 'page';
var PARAMETER_SORTING = 'sorting';
var PARAMETER_ITEMS = 'items';

exports.default = {
    PARAMETER_ATTRIBUTES: PARAMETER_ATTRIBUTES,
    PARAMETER_PAGE: PARAMETER_PAGE,
    PARAMETER_SORTING: PARAMETER_SORTING,
    PARAMETER_ITEMS: PARAMETER_ITEMS
};

},{}],15:[function(require,module,exports){
"use strict";

Vue.directive("render-category", {
    bind: function bind(el, binding) {
        el.onclick = function (event) {
            event.preventDefault();

            window.open(event.target.href, '_self');
        };
    }
});

},{}],16:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
    value: true
});

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

exports.default = {
    props: ["template", "facet"],

    data: function data() {
        return {
            isOpen: false
        };
    },

    created: function created() {
        this.$options.template = this.template || "#vue-item-dropdown";
    },


    computed: _extends({}, Vuex.mapState({
        isLoading: function isLoading(state) {
            return state.itemList.isLoading;
        }
    })),

    methods: {
        selected: function selected(value) {
            this.updateSelectedFilters(this.facet.id, value);
        },

        close: function close() {
            this.isOpen = false;
        },

        toggle: function toggle() {
            this.isOpen = !this.isOpen;
        }
    }
};

},{}],17:[function(require,module,exports){
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
        /*
         * Plentymarkets standard method for parsing params from string into object
         *
         * @param {string} urlParams
         * @returns {Object}
         */
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


        /*
         * Findologic method for parsing url params into a parameter map from current url
         * Taken from direct integration flUtils class
         *
         * @returns {{}} The parameter map
         */
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
                value = tmp.length < 2 ? '' : fixStr(tmp[1]).replace(/\+/g, ' ');

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

            if (requestParameters[_constants2.default.PARAMETER_ATTRIBUTES] === '') {
                delete requestParameters[_constants2.default.PARAMETER_ATTRIBUTES];
            }

            return requestParameters;
        },


        /*
         * Update url with selected filters
         *
         * @param {string} facetId
         * @param {string|array} facetValue
         */
        updateSelectedFilters: function updateSelectedFilters(facetId, facetValue) {
            var params = this.getSearchParams();

            if (!(_constants2.default.PARAMETER_ATTRIBUTES in params)) {
                params[_constants2.default.PARAMETER_ATTRIBUTES] = {};
            }

            var attributes = params[_constants2.default.PARAMETER_ATTRIBUTES];

            if (facetId === 'price' || this.facet.findologicFilterType === 'range-slider') {
                attributes[facetId] = {
                    min: facetValue.min,
                    max: facetValue.max
                };
            } else if (this.facet.select === 'single') {
                if (attributes[facetId] && Object.values(attributes[facetId]).includes(facetValue)) {
                    if (this.facet.id === 'cat' && facetValue.includes('_')) {
                        // Subcategory deselection
                        attributes[facetId] = [facetValue.split('_')[0]];
                    } else {
                        var index = Object.values(attributes[facetId]).indexOf(facetValue);
                        delete attributes[facetId][index];
                    }
                } else {
                    attributes[facetId] = [facetValue];
                }
            } else {
                if (!(facetId in attributes)) {
                    attributes[facetId] = [facetValue];
                } else {
                    var valueId = this.getKeyByValue(attributes[facetId], facetValue);

                    if (valueId === -1) {
                        var _index = Object.keys(attributes[facetId]).length;
                        attributes[facetId][_index] = facetValue;
                    } else {
                        delete attributes[facetId][valueId];
                    }
                }
            }

            params[_constants2.default.PARAMETER_ATTRIBUTES] = attributes;
            delete params[_constants2.default.PARAMETER_PAGE];

            document.location.search = '?' + $.param(params);
        },


        /*
         * Check if value is selected
         *
         * @param {string} facetId
         * @param {string} facetValue
         * @returns {boolean}
         */
        isValueSelected: function isValueSelected(facetId, facetValue) {
            var params = this.getSearchParams();

            if (!(_constants2.default.PARAMETER_ATTRIBUTES in params)) {
                return false;
            }

            var attributes = params[_constants2.default.PARAMETER_ATTRIBUTES];

            if (!(facetId in attributes)) {
                return false;
            } else if (facetId !== 'cat' && this.facet.select === 'single' && attributes[facetId] === facetValue) {
                return true;
            } else if (facetId === 'cat') {
                return this.getKeyBySuffix(attributes[facetId], facetValue) !== -1;
            } else if (this.getKeyByValue(attributes[facetId], facetValue) !== -1) {
                return true;
            } else {
                return false;
            }
        },


        /*
         * Get the list of selected filters from url
         *
         * @returns {Object}
         */
        getSelectedFilters: function getSelectedFilters() {
            var selectedFilters = [];
            var params = this.getSearchParams();

            if (!(_constants2.default.PARAMETER_ATTRIBUTES in params)) {
                return selectedFilters;
            }

            var attributes = params[_constants2.default.PARAMETER_ATTRIBUTES];

            for (var filter in attributes) {
                if (filter === 'wizard') {
                    continue;
                }

                if (filter === 'price' || this.isRangeSliderFilter(attributes[filter])) {
                    var facetInfo = this.getFacetIdInfoMap();

                    var unit = facetInfo[filter] && facetInfo[filter].unit ? ' ' + facetInfo[filter].unit : '';

                    selectedFilters.push({
                        id: filter,
                        name: attributes[filter].min + unit + ' - ' + attributes[filter].max + unit
                    });

                    continue;
                }

                if (_typeof(attributes[filter]) === 'object') {
                    var values = attributes[filter];
                    for (var value in values) {
                        selectedFilters.push({
                            id: filter,
                            name: values[value].replace(/_/g, " > ")
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


        /**
         * @param attributeValue
         * @returns {boolean}
         */
        isRangeSliderFilter: function isRangeSliderFilter(attributeValue) {
            return typeof attributeValue.min !== 'undefined' && typeof attributeValue.max !== 'undefined';
        },


        /*
         * Remove selected filter from url
         *
         * @param {string} facetId
         * @param {string} facetValue
         */
        removeSelectedFilter: function removeSelectedFilter(facetId, facetValue) {
            facetValue = facetValue.replace(' > ', '_');
            var params = this.getSearchParams();
            var attributes = params[_constants2.default.PARAMETER_ATTRIBUTES];

            if (_typeof(attributes[facetId]) !== 'object' || facetId === 'price' || this.isRangeSliderFilter(attributes[facetId])) {
                delete attributes[facetId];
            } else {
                var values = attributes[facetId];
                for (var value in values) {
                    if (values[value] === facetValue) {
                        delete attributes[facetId][value];
                    }
                }
            }

            params[_constants2.default.PARAMETER_ATTRIBUTES] = attributes;
            delete params[_constants2.default.PARAMETER_PAGE];

            document.location.search = '?' + $.param(params);
        },


        /*
         * Get selected filter value from url
         *
         * @param {string} facetId
         * @returns {Object|null}
         */
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


        /*
         * Get simple url parameter value
         *
         * @param {string} key
         * @returns {string|null}
         */
        getUrlParamValue: function getUrlParamValue(key) {
            var params = this.getSearchParams();

            if (!(key in params)) {
                return null;
            }

            return params[key];
        },


        /*
         * Get simple url parameter value
         *
         * @param {string} key
         * @param {string|array} value
         */
        setUrlParamValue: function setUrlParamValue(key, value) {
            var params = this.getSearchParams();

            params[key] = value;

            document.location.search = '?' + $.param(params);
        },


        /**
         * Set multiple url parameter values
         *
         * @param {array} keyValueArray
         */
        setUrlParamValues: function setUrlParamValues(keyValueArray) {
            var params = this.getSearchParams();

            keyValueArray.forEach(function (keyValueObject) {
                params[keyValueObject.key] = keyValueObject.value;
            });

            document.location.search = '?' + $.param(params);
        },


        /*
         * Get key from object by value
         *
         * @param {Object} object
         * @param {string} value
         * @returns {string|number}
         */
        getKeyByValue: function getKeyByValue(object, value) {
            for (var prop in object) {
                if (object.hasOwnProperty(prop)) {
                    if (object[prop] === value) {
                        return prop;
                    }
                }
            }

            return -1;
        },


        /*
         * Get key from object by value suffix
         *
         * @param {Object} object
         * @param {string} value
         * @returns {string|number}
         */
        getKeyBySuffix: function getKeyBySuffix(object, value) {
            for (var prop in object) {
                if (object.hasOwnProperty(prop)) {
                    if (object[prop].endsWith(value)) {
                        return prop;
                    }
                }
            }

            return -1;
        },


        /**
         *  Remove all `attrib` url params and reload the page
         */
        removeAllAttribsAndRefresh: function removeAllAttribsAndRefresh() {
            var params = this.getSearchParams();
            delete params[_constants2.default.PARAMETER_PAGE];
            delete params[_constants2.default.PARAMETER_ATTRIBUTES];
            document.location.search = '?' + $.param(params);
        },
        getFacetIdInfoMap: function getFacetIdInfoMap() {
            var map = {};

            this.$store.state.itemList.facets.forEach(function (facet) {
                map[facet.id] = facet;
            });

            return map;
        }
    }
};

},{"../constants":14}]},{},[8,10,11,12,13,6,7,5,9,1,3,4,2,15])


//# sourceMappingURL=filters-component.js.map
