(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
'use strict';

Vue.component("item-search", {

    props: ['template'],

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


    methods: {
        prepareSearch: function prepareSearch() {
            this.search();

            $('#searchBox').collapse('hide');
        },
        search: function search() {
            var searchBaseURL = '/search?query=';

            if (App.defaultLanguage !== App.language) {
                searchBaseURL = '/' + App.language + '/search?query=';
            }

            window.open(searchBaseURL + this.$refs.searchInput.value, '_self', false);
        },
        autocomplete: function autocomplete(searchString) {},
        selectAutocompleteItem: function selectAutocompleteItem(item) {},
        keyup: function keyup() {},
        keydown: function keydown() {},


        // hide autocomplete after 100ms to make clicking on it possible
        setIsSearchFocused: function setIsSearchFocused(value) {
            var _this = this;

            setTimeout(function () {
                _this.isSearchFocused = !!value;
            }, 100);
        }
    }
});

},{}],2:[function(require,module,exports){
"use strict";

Vue.directive("render-category", {
    bind: function bind(el, binding) {
        el.onclick = function (event) {
            event.preventDefault();

            window.open(event.target.href, '_self');
        };
    }
});

},{}]},{},[1,2])


//# sourceMappingURL=filters-component.js.map
