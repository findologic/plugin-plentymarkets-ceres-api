export interface Facet {
    id: string;
    values: FacetValue[];
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
