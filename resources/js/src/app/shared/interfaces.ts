import { Store } from 'vuex';

export interface TemplateOverridable {
    template?: string;
}

export interface FacetAware {
    facet: Facet;
}

export interface State {
    itemList: ItemListData;
}

export interface Facet {
    id: string;
    name?: string;
    type?: string;
    isMain?: boolean;
    itemCount?: number;
    select?: string;
    values?: FacetValue[];
    findologicFilterType?: string;
    minValue?: string;
    min?: string;
    maxValue?: string;
    max?: string;
    step?: number;
    unit?: string;
    noAvailableFiltersText?: string;
    cssClass?: string;
}

export interface FacetValue {
    count?: string;
    image?: string;
    id?: string;
    name: string;
    imageUrl?: string;
    items?: [];
    position?: string;
    selected?: boolean;
}

export interface ColorFacet extends Facet {
    values: ColorFacetValue[];
}

export interface ColorFacetValue extends FacetValue {
    hexValue: string|null;
    colorImageUrl?: string|null;
}

export interface ItemListData {
    isLoading?: boolean;
    selectedFacets: Facet[];
    facets: Facet[];
}

export interface StoreState {
    itemList: ItemListData;
}

export interface PlentyVuexStore extends Store<StoreState> {
    itemList: ItemListData;
}

export interface JQuery {
    collapse: () => {};
}
