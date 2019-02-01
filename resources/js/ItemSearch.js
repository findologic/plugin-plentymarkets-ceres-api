import Url from "./mixins/url";

Vue.component("item-search", {
    mixins: [Url],
    delimiters: ["${", "}"],
    props: [
        "template"
    ],

    data()
    {
        return {
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
            const urlParams = Url.getUrlParams(document.location.search);

            this.$store.commit("setItemListSearchString", urlParams.query);

            if (urlParams === null || typeof urlParams === 'undefined') {
                this.$refs.searchInput.value = '';
            } else {
                this.$refs.searchInput.value = urlParams.query;
            }
        });
    },

    methods:
        {
            prepareSearch()
            {
                this.search();

                $("#searchBox").collapse("hide");
            },

            search()
            {
                let searchBaseURL = "/search?query=";

                if (App.defaultLanguage !== App.language)
                {
                    searchBaseURL = `/${App.language}/search?query=`;
                }

                window.open(searchBaseURL + this.$refs.searchInput.value, "_self", false);
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
