import Url from '../../mixins/url';
import Vue from 'vue';

Vue.component('item-search', {
    mixins: [Url],

    props: {
        template:
        {
            type: String,
            default: '#vue-item-search'
        },
        showItemImages: {
            type: Boolean, default: false
        },
        forwardToSingleItem: {
            type: Boolean,
            // eslint-disable-next-line no-undef
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
    },

    mounted()
    {
        this.$nextTick(() =>
        {
            const urlParams = this.getUrlParams(document.location.search);

            this.$store.commit('setItemListSearchString', urlParams.query);

            const rawQuery = urlParams.query ? urlParams.query : '';
            // Manually regex out all "+" signs as decodeURIComponent does not take care of that.
            // If we wouldn't replace them with spaces, "+" signs would be displayed in the search field.
            this.$refs.searchInput.value = decodeURIComponent(rawQuery.replace(/\+/g, ' '));
        });
    },

    methods:
    {
        prepareSearch()
        {
            // eslint-disable-next-line no-undef
            $('#searchBox').collapse('hide');
        },

        search()
        {
            let searchBaseURL = '/search?query=';

            // eslint-disable-next-line no-undef
            if (App.defaultLanguage !== App.language)
            {
                // eslint-disable-next-line no-undef
                searchBaseURL = `/${App.language}/search?query=`;
            }

            window.open(searchBaseURL + this.$refs.searchInput.value, '_self', false);
        },


        autocomplete(searchString)
        {
            // Nothing to do.
        ,
        
        selectAutocompleteItem(item)
        {
            // Nothing to do.
        },

        keyup()
        {
            // Nothing to do.
        },

        keydown()
        {
            // Nothing to do.
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
