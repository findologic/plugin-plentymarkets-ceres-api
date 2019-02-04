Vue.component("item-search", {
    computed:
    {
        selectedAutocompleteItem()
        {
            return null;
        }
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
        }
    }
});
