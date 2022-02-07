(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
/**
 * SVGInjector v1.1.3 - Fast, caching, dynamic inline SVG DOM injection library
 * https://github.com/iconic/SVGInjector
 *
 * Copyright (c) 2014-2015 Waybury <hello@waybury.com>
 * @license MIT
 */

(function (window, document) {

  'use strict';

  // Environment
  var isLocal = window.location.protocol === 'file:';
  var hasSvgSupport = document.implementation.hasFeature('http://www.w3.org/TR/SVG11/feature#BasicStructure', '1.1');

  function uniqueClasses(list) {
    list = list.split(' ');

    var hash = {};
    var i = list.length;
    var out = [];

    while (i--) {
      if (!hash.hasOwnProperty(list[i])) {
        hash[list[i]] = 1;
        out.unshift(list[i]);
      }
    }

    return out.join(' ');
  }

  /**
   * cache (or polyfill for <= IE8) Array.forEach()
   * source: https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/forEach
   */
  var forEach = Array.prototype.forEach || function (fn, scope) {
    if (this === void 0 || this === null || typeof fn !== 'function') {
      throw new TypeError();
    }

    /* jshint bitwise: false */
    var i, len = this.length >>> 0;
    /* jshint bitwise: true */

    for (i = 0; i < len; ++i) {
      if (i in this) {
        fn.call(scope, this[i], i, this);
      }
    }
  };

  // SVG Cache
  var svgCache = {};

  var injectCount = 0;
  var injectedElements = [];

  // Request Queue
  var requestQueue = [];

  // Script running status
  var ranScripts = {};

  var cloneSvg = function (sourceSvg) {
    return sourceSvg.cloneNode(true);
  };

  var queueRequest = function (url, callback) {
    requestQueue[url] = requestQueue[url] || [];
    requestQueue[url].push(callback);
  };

  var processRequestQueue = function (url) {
    for (var i = 0, len = requestQueue[url].length; i < len; i++) {
      // Make these calls async so we avoid blocking the page/renderer
      /* jshint loopfunc: true */
      (function (index) {
        setTimeout(function () {
          requestQueue[url][index](cloneSvg(svgCache[url]));
        }, 0);
      })(i);
      /* jshint loopfunc: false */
    }
  };

  var loadSvg = function (url, callback) {
    if (svgCache[url] !== undefined) {
      if (svgCache[url] instanceof SVGSVGElement) {
        // We already have it in cache, so use it
        callback(cloneSvg(svgCache[url]));
      }
      else {
        // We don't have it in cache yet, but we are loading it, so queue this request
        queueRequest(url, callback);
      }
    }
    else {

      if (!window.XMLHttpRequest) {
        callback('Browser does not support XMLHttpRequest');
        return false;
      }

      // Seed the cache to indicate we are loading this URL already
      svgCache[url] = {};
      queueRequest(url, callback);

      var httpRequest = new XMLHttpRequest();

      httpRequest.onreadystatechange = function () {
        // readyState 4 = complete
        if (httpRequest.readyState === 4) {

          // Handle status
          if (httpRequest.status === 404 || httpRequest.responseXML === null) {
            callback('Unable to load SVG file: ' + url);

            if (isLocal) callback('Note: SVG injection ajax calls do not work locally without adjusting security setting in your browser. Or consider using a local webserver.');

            callback();
            return false;
          }

          // 200 success from server, or 0 when using file:// protocol locally
          if (httpRequest.status === 200 || (isLocal && httpRequest.status === 0)) {

            /* globals Document */
            if (httpRequest.responseXML instanceof Document) {
              // Cache it
              svgCache[url] = httpRequest.responseXML.documentElement;
            }
            /* globals -Document */

            // IE9 doesn't create a responseXML Document object from loaded SVG,
            // and throws a "DOM Exception: HIERARCHY_REQUEST_ERR (3)" error when injected.
            //
            // So, we'll just create our own manually via the DOMParser using
            // the the raw XML responseText.
            //
            // :NOTE: IE8 and older doesn't have DOMParser, but they can't do SVG either, so...
            else if (DOMParser && (DOMParser instanceof Function)) {
              var xmlDoc;
              try {
                var parser = new DOMParser();
                xmlDoc = parser.parseFromString(httpRequest.responseText, 'text/xml');
              }
              catch (e) {
                xmlDoc = undefined;
              }

              if (!xmlDoc || xmlDoc.getElementsByTagName('parsererror').length) {
                callback('Unable to parse SVG file: ' + url);
                return false;
              }
              else {
                // Cache it
                svgCache[url] = xmlDoc.documentElement;
              }
            }

            // We've loaded a new asset, so process any requests waiting for it
            processRequestQueue(url);
          }
          else {
            callback('There was a problem injecting the SVG: ' + httpRequest.status + ' ' + httpRequest.statusText);
            return false;
          }
        }
      };

      httpRequest.open('GET', url);

      // Treat and parse the response as XML, even if the
      // server sends us a different mimetype
      if (httpRequest.overrideMimeType) httpRequest.overrideMimeType('text/xml');

      httpRequest.send();
    }
  };

  // Inject a single element
  var injectElement = function (el, evalScripts, pngFallback, callback) {

    // Grab the src or data-src attribute
    var imgUrl = el.getAttribute('data-src') || el.getAttribute('src');

    // We can only inject SVG
    if (!(/\.svg/i).test(imgUrl)) {
      callback('Attempted to inject a file with a non-svg extension: ' + imgUrl);
      return;
    }

    // If we don't have SVG support try to fall back to a png,
    // either defined per-element via data-fallback or data-png,
    // or globally via the pngFallback directory setting
    if (!hasSvgSupport) {
      var perElementFallback = el.getAttribute('data-fallback') || el.getAttribute('data-png');

      // Per-element specific PNG fallback defined, so use that
      if (perElementFallback) {
        el.setAttribute('src', perElementFallback);
        callback(null);
      }
      // Global PNG fallback directoriy defined, use the same-named PNG
      else if (pngFallback) {
        el.setAttribute('src', pngFallback + '/' + imgUrl.split('/').pop().replace('.svg', '.png'));
        callback(null);
      }
      // um...
      else {
        callback('This browser does not support SVG and no PNG fallback was defined.');
      }

      return;
    }

    // Make sure we aren't already in the process of injecting this element to
    // avoid a race condition if multiple injections for the same element are run.
    // :NOTE: Using indexOf() only _after_ we check for SVG support and bail,
    // so no need for IE8 indexOf() polyfill
    if (injectedElements.indexOf(el) !== -1) {
      return;
    }

    // Remember the request to inject this element, in case other injection
    // calls are also trying to replace this element before we finish
    injectedElements.push(el);

    // Try to avoid loading the orginal image src if possible.
    el.setAttribute('src', '');

    // Load it up
    loadSvg(imgUrl, function (svg) {

      if (typeof svg === 'undefined' || typeof svg === 'string') {
        callback(svg);
        return false;
      }

      var imgId = el.getAttribute('id');
      if (imgId) {
        svg.setAttribute('id', imgId);
      }

      var imgTitle = el.getAttribute('title');
      if (imgTitle) {
        svg.setAttribute('title', imgTitle);
      }

      // Concat the SVG classes + 'injected-svg' + the img classes
      var classMerge = [].concat(svg.getAttribute('class') || [], 'injected-svg', el.getAttribute('class') || []).join(' ');
      svg.setAttribute('class', uniqueClasses(classMerge));

      var imgStyle = el.getAttribute('style');
      if (imgStyle) {
        svg.setAttribute('style', imgStyle);
      }

      // Copy all the data elements to the svg
      var imgData = [].filter.call(el.attributes, function (at) {
        return (/^data-\w[\w\-]*$/).test(at.name);
      });
      forEach.call(imgData, function (dataAttr) {
        if (dataAttr.name && dataAttr.value) {
          svg.setAttribute(dataAttr.name, dataAttr.value);
        }
      });

      // Make sure any internally referenced clipPath ids and their
      // clip-path references are unique.
      //
      // This addresses the issue of having multiple instances of the
      // same SVG on a page and only the first clipPath id is referenced.
      //
      // Browsers often shortcut the SVG Spec and don't use clipPaths
      // contained in parent elements that are hidden, so if you hide the first
      // SVG instance on the page, then all other instances lose their clipping.
      // Reference: https://bugzilla.mozilla.org/show_bug.cgi?id=376027

      // Handle all defs elements that have iri capable attributes as defined by w3c: http://www.w3.org/TR/SVG/linking.html#processingIRI
      // Mapping IRI addressable elements to the properties that can reference them:
      var iriElementsAndProperties = {
        'clipPath': ['clip-path'],
        'color-profile': ['color-profile'],
        'cursor': ['cursor'],
        'filter': ['filter'],
        'linearGradient': ['fill', 'stroke'],
        'marker': ['marker', 'marker-start', 'marker-mid', 'marker-end'],
        'mask': ['mask'],
        'pattern': ['fill', 'stroke'],
        'radialGradient': ['fill', 'stroke']
      };

      var element, elementDefs, properties, currentId, newId;
      Object.keys(iriElementsAndProperties).forEach(function (key) {
        element = key;
        properties = iriElementsAndProperties[key];

        elementDefs = svg.querySelectorAll('defs ' + element + '[id]');
        for (var i = 0, elementsLen = elementDefs.length; i < elementsLen; i++) {
          currentId = elementDefs[i].id;
          newId = currentId + '-' + injectCount;

          // All of the properties that can reference this element type
          var referencingElements;
          forEach.call(properties, function (property) {
            // :NOTE: using a substring match attr selector here to deal with IE "adding extra quotes in url() attrs"
            referencingElements = svg.querySelectorAll('[' + property + '*="' + currentId + '"]');
            for (var j = 0, referencingElementLen = referencingElements.length; j < referencingElementLen; j++) {
              referencingElements[j].setAttribute(property, 'url(#' + newId + ')');
            }
          });

          elementDefs[i].id = newId;
        }
      });

      // Remove any unwanted/invalid namespaces that might have been added by SVG editing tools
      svg.removeAttribute('xmlns:a');

      // Post page load injected SVGs don't automatically have their script
      // elements run, so we'll need to make that happen, if requested

      // Find then prune the scripts
      var scripts = svg.querySelectorAll('script');
      var scriptsToEval = [];
      var script, scriptType;

      for (var k = 0, scriptsLen = scripts.length; k < scriptsLen; k++) {
        scriptType = scripts[k].getAttribute('type');

        // Only process javascript types.
        // SVG defaults to 'application/ecmascript' for unset types
        if (!scriptType || scriptType === 'application/ecmascript' || scriptType === 'application/javascript') {

          // innerText for IE, textContent for other browsers
          script = scripts[k].innerText || scripts[k].textContent;

          // Stash
          scriptsToEval.push(script);

          // Tidy up and remove the script element since we don't need it anymore
          svg.removeChild(scripts[k]);
        }
      }

      // Run/Eval the scripts if needed
      if (scriptsToEval.length > 0 && (evalScripts === 'always' || (evalScripts === 'once' && !ranScripts[imgUrl]))) {
        for (var l = 0, scriptsToEvalLen = scriptsToEval.length; l < scriptsToEvalLen; l++) {

          // :NOTE: Yup, this is a form of eval, but it is being used to eval code
          // the caller has explictely asked to be loaded, and the code is in a caller
          // defined SVG file... not raw user input.
          //
          // Also, the code is evaluated in a closure and not in the global scope.
          // If you need to put something in global scope, use 'window'
          new Function(scriptsToEval[l])(window); // jshint ignore:line
        }

        // Remember we already ran scripts for this svg
        ranScripts[imgUrl] = true;
      }

      // :WORKAROUND:
      // IE doesn't evaluate <style> tags in SVGs that are dynamically added to the page.
      // This trick will trigger IE to read and use any existing SVG <style> tags.
      //
      // Reference: https://github.com/iconic/SVGInjector/issues/23
      var styleTags = svg.querySelectorAll('style');
      forEach.call(styleTags, function (styleTag) {
        styleTag.textContent += '';
      });

      // Replace the image with the svg
      el.parentNode.replaceChild(svg, el);

      // Now that we no longer need it, drop references
      // to the original element so it can be GC'd
      delete injectedElements[injectedElements.indexOf(el)];
      el = null;

      // Increment the injected count
      injectCount++;

      callback(svg);
    });
  };

  /**
   * SVGInjector
   *
   * Replace the given elements with their full inline SVG DOM elements.
   *
   * :NOTE: We are using get/setAttribute with SVG because the SVG DOM spec differs from HTML DOM and
   * can return other unexpected object types when trying to directly access svg properties.
   * ex: "className" returns a SVGAnimatedString with the class value found in the "baseVal" property,
   * instead of simple string like with HTML Elements.
   *
   * @param {mixes} Array of or single DOM element
   * @param {object} options
   * @param {function} callback
   * @return {object} Instance of SVGInjector
   */
  var SVGInjector = function (elements, options, done) {

    // Options & defaults
    options = options || {};

    // Should we run the scripts blocks found in the SVG
    // 'always' - Run them every time
    // 'once' - Only run scripts once for each SVG
    // [false|'never'] - Ignore scripts
    var evalScripts = options.evalScripts || 'always';

    // Location of fallback pngs, if desired
    var pngFallback = options.pngFallback || false;

    // Callback to run during each SVG injection, returning the SVG injected
    var eachCallback = options.each;

    // Do the injection...
    if (elements.length !== undefined) {
      var elementsLoaded = 0;
      forEach.call(elements, function (element) {
        injectElement(element, evalScripts, pngFallback, function (svg) {
          if (eachCallback && typeof eachCallback === 'function') eachCallback(svg);
          if (done && elements.length === ++elementsLoaded) done(elementsLoaded);
        });
      });
    }
    else {
      if (elements) {
        injectElement(elements, evalScripts, pngFallback, function (svg) {
          if (eachCallback && typeof eachCallback === 'function') eachCallback(svg);
          if (done) done(1);
          elements = null;
        });
      }
      else {
        if (done) done(0);
      }
    }
  };

  /* global module, exports: true, define */
  // Node.js or CommonJS
  if (typeof module === 'object' && typeof module.exports === 'object') {
    module.exports = exports = SVGInjector;
  }
  // AMD support
  else if (typeof define === 'function' && define.amd) {
    define(function () {
      return SVGInjector;
    });
  }
  // Otherwise, attach to window as global
  else if (typeof window === 'object') {
    window.SVGInjector = SVGInjector;
  }
  /* global -module, -exports, -define */

}(window, document));

},{}],2:[function(require,module,exports){
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

},{"../../constants":15,"../../mixins/url":18}],3:[function(require,module,exports){
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

},{"../../mixins/url":18}],4:[function(require,module,exports){
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

},{"../../constants":15,"../../mixins/url":18}],5:[function(require,module,exports){
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

},{"../../mixins/url":18}],6:[function(require,module,exports){
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

},{"../../../mixins/baseDropdown":17,"../../../mixins/url":18}],7:[function(require,module,exports){
'use strict';

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

var _url = require('../../../mixins/url');

var _url2 = _interopRequireDefault(_url);

var _svgInjector = require('svg-injector');

var _svgInjector2 = _interopRequireDefault(_svgInjector);

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
            (0, _svgInjector2.default)($('img.fl-svg'));
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

},{"../../../mixins/url":18,"svg-injector":1}],8:[function(require,module,exports){
'use strict';

var _url = require('../../../mixins/url');

var _url2 = _interopRequireDefault(_url);

var _baseDropdown = require('../../../mixins/baseDropdown');

var _baseDropdown2 = _interopRequireDefault(_baseDropdown);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

Vue.component("item-dropdown", {
    mixins: [_url2.default, _baseDropdown2.default]
});

},{"../../../mixins/baseDropdown":17,"../../../mixins/url":18}],9:[function(require,module,exports){
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

},{"../../../mixins/url":18}],10:[function(require,module,exports){
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

},{"../../../mixins/url":18}],11:[function(require,module,exports){
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

},{}],12:[function(require,module,exports){
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

},{"../../../mixins/url":18}],13:[function(require,module,exports){
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

},{"../../../mixins/url":18}],14:[function(require,module,exports){
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
                    'min': Math.min(self.valueFrom, self.facet.minValue),
                    'max': Math.max(self.valueTo, self.facet.maxValue)
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

},{"../../../mixins/url":18}],15:[function(require,module,exports){
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

},{}],16:[function(require,module,exports){
"use strict";

Vue.directive("render-category", {
    bind: function bind(el, binding) {
        el.onclick = function (event) {
            event.preventDefault();

            window.open(event.target.href, '_self');
        };
    }
});

},{}],17:[function(require,module,exports){
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

},{}],18:[function(require,module,exports){
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

},{"../constants":15}]},{},[9,11,12,13,14,7,8,6,10,2,4,5,3,16])


//# sourceMappingURL=filters-component.js.map
