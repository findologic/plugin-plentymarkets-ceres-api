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
    selectMode?: string;
    values?: FacetValue[];
    findologicFilterType?: string;
    // minValue?: number;
    min?: number;
    // maxValue?: number;
    max?: number;
    step?: number;
    unit?: string;
    noAvailableFiltersText?: string;
    cssClass?: string;
}

export interface Media {
    url?: string;
}

export interface TranslatedName {
    name?: string;
}

export interface FacetValue {
    frequency?: number;
    id?: string;
    translated: TranslatedName;
    media?: Media;
    selected: boolean;
    values: FacetValue[];
}

export interface ColorFacet extends Facet {
    values: ColorFacetValue[];
}

export interface ColorFacetValue extends FacetValue {
    hexValue: string|null;
    colorImageUrl?: string|null;
}

export interface CategoryFacet extends Facet {
    values: CategoryFacetValue[];
}

export interface CategoryFacetValue extends FacetValue {
    items: CategoryFacetValue[];
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
    collapse: () => void;
}

export interface Promotion {
    link: string;
    image: string;
}