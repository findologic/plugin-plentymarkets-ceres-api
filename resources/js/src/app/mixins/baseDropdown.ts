import Vue from 'vue';
import Component from 'vue-class-component';
import { Facet } from '../shared/interfaces';
import { Mixins } from 'vue-property-decorator';
import UrlBuilder from '../shared/UrlBuilder';

const BaseDropdownProps = Vue.extend({
    props: {
        facet: {
            type: Object,
            required: true
        },
        template: {
            type: String,
            default: null
        }
    }
});

interface BaseDropdownInterface {
    facet: Facet;
    template?: string|null;
}

@Component({
    computed: {
        isLoading() {
            return this.$store.state.itemList.isLoading;
        }
    }
})
export default class BaseDropdown extends Mixins<Vue, BaseDropdownInterface>(Vue, BaseDropdownProps) {
    protected isOpen = false;

    get facetData(): Facet {
        return this.facet;
    }

    created() {
        this.$options.template = this.template || '#vue-item-dropdown';
    }

    selected(value: string) {
        UrlBuilder.updateSelectedFilters(this.facet, this.facetData.id, value);
    }

    close() {
        this.isOpen = false;
    }

    toggle() {
        this.isOpen = !this.isOpen;
    }
}
