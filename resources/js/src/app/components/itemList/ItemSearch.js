import url from "../../mixins/url";

Vue.component("item-search", {
    mixins: [url],

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

    data()
    {
        return {
            promiseCount: 0,
            autocompleteResult: [],
            selectedAutocompleteIndex: -1,
            isSearchFocused: false
        };
    },


    computed:
    {
        selectedAutocompleteItem()
        {
            return null;
        }
    },

    created()
    {
        this.$options.template = this.template;
        // Ensure item-search template is loaded for Smart Suggest initialization
        this.$store.dispatch('loadComponent', 'item-search')
    },

    mounted()
    {
        this.$nextTick(() =>
        {
            const urlParams = this.getUrlParams(document.location.search);

            this.$store.commit("setItemListSearchString", urlParams.query);

            let rawQuery = urlParams.query ? urlParams.query : '';
            // Manually regex out all "+" signs as decodeURIComponent does not take care of that.
            // If we wouldn't replace them with spaces, "+" signs would be displayed in the search field.
            this.$refs.searchInput.value = decodeURIComponent(rawQuery.replace(/\+/g, ' '));
        });
    },

    methods:
    {
        prepareSearch()
        {
            $('#searchBox').collapse('hide');
        },

        search()
        {
            let searchBaseURL = '/search?query=';

            if (App.defaultLanguage !== App.language)
            {
                searchBaseURL = `/${App.language}/search?query=`;
            }

            window.open(searchBaseURL + this.$refs.searchInput.value, '_self', false);
        },

        autocomplete(searchString)
        {
        },

        selectAutocompleteItem(item)
        {
        },

        keyup()
        {
        },

        keydown()
        {
        },

        // hide autocomplete after 100ms to make clicking on it possible
        setIsSearchFocused(value)
        {
            setTimeout(() =>
            {
                this.isSearchFocused = !!value;
            }, 100);
        }
    }
});
