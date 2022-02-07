import url from '../../../mixins/url'
import { SVGInjector } from '@tanem/svg-injector'
import DOMPurify from 'dompurify'

Vue.component("item-color-tiles", {
    mixins: [url],

    props: [
        "template",
        "facet",
        "fallbackImage"
    ],

    created() {
        this.$options.template = this.template || "#vue-item-color-tiles";
    },

    computed: {
        ...Vuex.mapState({
            isLoading: state => state.itemList.isLoading,
        })
    },

    mounted() {
        this.$nextTick(function () {
            SVGInjector(document.getElementsByClassName('fl-svg'), {
                beforeEach(svg) {
                    DOMPurify.sanitize(svg, {
                        IN_PLACE: true,
                        USE_PROFILES: { svg: true, svgFilters: true }
                    })
                }
            })
        });
    },

    methods: {
        isSelected(facetValueName) {
            let facetValue = this.facet.values.filter((value) => value.name === facetValueName);

            return facetValue.length && this.isValueSelected(this.facet.id, facetValue[0].name);
        },

        tileClicked: function (value) {
            this.updateSelectedFilters(this.facet.id, value);
        },

        handleImageError: function(event, colorValue) {
            if (!colorValue.hexValue) {
                event.target.src = this.fallbackImage;
            } else {
                event.target.remove();
            }
        }
    }
});
