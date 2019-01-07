(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
Vue.component("findologic-filter-list", {

    delimiters: ["${", "}"],

    props: ["template", "facetData"],

    data() {
        return {
            isActive: false
        };
    },

    computed: Vuex.mapState({
        facets(state) {
            return state.itemList.facets.sort((facetA, facetB) => {
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

    created() {
        console.log('create findologic filters list');
        this.$store.commit("setFacets", this.facetData);

        this.$options.template = this.template || "#vue-findologic-filter-list";

        const urlParams = UrlService.getUrlParams(document.location.search);

        let selectedFacets = [];

        if (urlParams.facets) {
            selectedFacets = urlParams.facets.split(",");
        }

        if (urlParams.priceMin || urlParams.priceMax) {
            const priceMin = urlParams.priceMin || "";
            const priceMax = urlParams.priceMax || "";

            this.$store.commit("setPriceFacet", { priceMin: priceMin, priceMax: priceMax });

            selectedFacets.push("price");
        }

        if (selectedFacets.length > 0) {
            this.$store.commit("setSelectedFacetsByIds", selectedFacets);
        }
    },

    methods: {
        toggleOpeningState() {
            window.setTimeout(() => {
                this.isActive = !this.isActive;
            }, 300);
        }
    }
});

},{}]},{},[1])


//# sourceMappingURL=filters-component.js.map
