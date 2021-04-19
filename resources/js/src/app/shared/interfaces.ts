export interface TemplateOverridable {
    template?: string;
}

export interface FacetAware {
    facet: Facet;
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
}

export interface FacetValue {
    name: string;
}

export interface ColorFacet extends Facet {
    values: ColorFacetValue[];
}

export interface ColorFacetValue extends FacetValue {
    hexValue: string|null;
    colorImageUrl?: string|null;
}

export interface ItemListData {
    isLoading: boolean;
}
