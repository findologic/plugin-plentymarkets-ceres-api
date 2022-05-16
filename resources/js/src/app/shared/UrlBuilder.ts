import Constants from './constants';
import { Facet, PlentyVuexStore } from './interfaces';

class UrlBuilder {
    /**
     * Plentymarkets standard method for parsing params from string into object
     */
    public getUrlParams(urlParams?: string|null): UrlParams {
        if (!urlParams) {
            return {} as UrlParams;
        }

        let tokens;
        const params = {} as UrlParams;
        const regex = /[?&]?([^=]+)=([^&]*)/g;

        urlParams = urlParams.split('+').join(' ');
        while ((tokens = regex.exec(urlParams)) !== null) {
            params[decodeURIComponent(tokens[1])] = decodeURIComponent(tokens[2]);
        }

        return params;
    }

    /**
     * Findologic method for parsing url params into a parameter map from current url
     * Taken from direct integration flUtils class
     */
    getSearchParams(): UrlParams {
        let queryString = document.location.search;
        const requestParameters = {} as UrlParams;

        // Remove any leading ? as it is not part of the query string but will be passed due to
        // the way we use parseQueryString.
        if (typeof queryString !== 'undefined') {
            queryString = queryString.replace(/^\?/, '');
        } else {
            queryString = '';
        }

        const strArr = String(queryString)
            .replace(/^&/, '')
            .replace(/&$/, '')
            .split('&');

        const sal = strArr.length;
        const fixStr = function(queryString: string) {
            return decodeURIComponent(queryString.replace(/\+/g, '%20'));
        };

        let i, j, ct, p, lastObj, obj, chr, tmp, key, value, postLeftBracketPos, keys, keysLen;

        for (i = 0; i < sal; i++) {
            tmp = strArr[i].split('=');
            key = fixStr(tmp[0]);
            value = (tmp.length < 2) ? '' : fixStr(tmp[1]).replace(/\+/g, ' ');

            while (key.charAt(0) === ' ') {
                key = key.slice(1);
            }
            if (key.indexOf('\x00') > -1) {
                key = key.slice(0, key.indexOf('\x00'));
            }
            if (key && key.charAt(0) !== '[') {
                keys = [];
                postLeftBracketPos = 0;
                for (j = 0; j < key.length; j++) {
                    if (key.charAt(j) === '[' && !postLeftBracketPos) {
                        postLeftBracketPos = j + 1;
                    } else if (key.charAt(j) === ']') {
                        if (postLeftBracketPos) {
                            if (!keys.length) {
                                keys.push(key.slice(0, postLeftBracketPos - 1));
                            }
                            keys.push(key.substr(postLeftBracketPos, j - postLeftBracketPos));
                            postLeftBracketPos = 0;
                            if (key.charAt(j + 1) !== '[') {
                                break;
                            }
                        }
                    }
                }
                if (!keys.length) {
                    keys = [key];
                }
                for (j = 0; j < keys[0].length; j++) {
                    chr = keys[0].charAt(j);
                    if (chr === ' ' || chr === '.' || chr === '[') {
                        keys[0] = keys[0].substr(0, j) + '_' + keys[0].substr(j + 1);
                    }
                    if (chr === '[') {
                        break;
                    }
                }

                // Taken from direct integration flUtils class
                // eslint-disable-next-line @typescript-eslint/no-explicit-any
                obj = requestParameters as any;
                for (j = 0, keysLen = keys.length; j < keysLen; j++) {
                    key = keys[j].replace(/^['"]/, '')
                        .replace(/['"]$/, '');
                    lastObj = obj;
                    if ((key !== '' && key !== ' ') || j === 0) {
                        if (typeof obj[key] === 'undefined') {
                            obj[key] = {};
                        }

                        obj = obj[key];
                    } else { // To insert new dimension
                        ct = -1;
                        for (p in obj) {
                            if (Object.prototype.hasOwnProperty.call(obj, p)) {
                                if (+p > ct && p.match(/^\d+$/g)) {
                                    ct = +p;
                                }
                            }
                        }
                        key = ct + 1;
                    }
                }
                lastObj[key] = value;
            }
        }

        if (requestParameters[Constants.PARAMETER_ATTRIBUTES] === '') {
            delete requestParameters[Constants.PARAMETER_ATTRIBUTES];
        }

        return requestParameters;
    }

    /**
     * Update url with selected filters
     */
    updateSelectedFilters(facet: Facet, facetId: string, facetValue: string|PriceFacetValue): void {
        const params = this.getSearchParams();

        if (!(Constants.PARAMETER_ATTRIBUTES in params)) {
            params[Constants.PARAMETER_ATTRIBUTES] = {};
        }

        const attributes = params[Constants.PARAMETER_ATTRIBUTES] as Attributes;

        if (facetId === 'price' || facet.findologicFilterType === 'range-slider') {
            const facetVal = facetValue as PriceFacetValue;

            if (facetVal.max !== Number.MAX_SAFE_INTEGER) {
                attributes[facetId] = {
                    min: facetVal.min,
                    max: facetVal.max
                };
            } else {
                attributes[facetId] = {
                    min: facetVal.min,
                };
            }
        } else if (facet.select === 'single') {
            const facetVal = facetValue as string;

            if (attributes[facetId] && Object.values(attributes[facetId]).includes(facetVal)) {
                if (facet.id === 'cat' && facetVal.includes('_') ) {
                    // Subcategory deselection
                    attributes[facetId] = [facetVal.split('_')[0]];
                } else {
                    const index = Object.values(attributes[facetId]).indexOf(facetVal);
                    delete attributes[facetId][index];
                }
            } else {
                attributes[facetId] = [facetVal];
            }
        } else {
            const facetVal = facetValue as string;

            if (!(facetId in attributes)) {
                attributes[facetId] = [facetVal];
            } else {
                const valueId = this.getKeyByValue(attributes[facetId], facetVal);

                if (valueId === -1) {
                    const index = Object.keys(attributes[facetId]).length;
                    attributes[facetId][index] = facetVal;
                } else {
                    delete attributes[facetId][valueId];
                }
            }
        }

        params[Constants.PARAMETER_ATTRIBUTES] = attributes;
        delete params[Constants.PARAMETER_PAGE];

        document.location.search = '?' + $.param(params);
    }

    /**
     * Check if value is selected
     */
    isValueSelected(facet: Facet, facetId: string, facetValue: string): boolean {
        const params = this.getSearchParams();

        if (!(Constants.PARAMETER_ATTRIBUTES in params)) {
            return false;
        }

        const attributes = params[Constants.PARAMETER_ATTRIBUTES] as Attributes;

        if (!(facetId in attributes)) {
            return false;
        } else if (facetId !== 'cat' && facet.select === 'single' && attributes[facetId] === facetValue) {
            return true;
        } else if (facetId === 'cat') {
            return this.getKeyBySuffix(attributes[facetId], facetValue) !== -1;
        } else if (this.getKeyByValue(attributes[facetId], facetValue) !== -1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the list of selected filters from url.
     */
    getSelectedFilters(store?: PlentyVuexStore): Facet[] {
        const selectedFilters = [] as Facet[];
        const params = this.getSearchParams();

        if (!(Constants.PARAMETER_ATTRIBUTES in params)) {
            return selectedFilters;
        }

        const attributes = params[Constants.PARAMETER_ATTRIBUTES] as Attributes;

        for (const filter in attributes) {
            if (!Object.prototype.hasOwnProperty.call(attributes, filter)) {
                continue;
            }

            if (filter === 'wizard') {
                continue;
            }

            if (filter === 'price' || this.isRangeSliderFilter(attributes[filter])) {
                const facetInfo = this.getFacetIdInfoMap(store);

                const unit = (facetInfo[filter] && facetInfo[filter].unit) ? ' ' + facetInfo[filter].unit : '';

                selectedFilters.push({
                    id: filter,
                    name: attributes[filter].min + unit + ' - ' + attributes[filter].max + unit
                });

                continue;
            }

            if (typeof attributes[filter] === 'object') {
                const values = attributes[filter];
                for (const value in values) {
                    if (!Object.prototype.hasOwnProperty.call(values, value)) {
                        continue;
                    }

                    selectedFilters.push({
                        id: filter,
                        name: values[value].replace(/_/g, ' > ')
                    });
                }

                continue;
            }

            selectedFilters.push({
                id: filter,
                name: attributes[filter]
            });
        }

        return selectedFilters;
    }

    isRangeSliderFilter(attributeValue: Attribute): boolean {
        return (typeof attributeValue.min !== 'undefined' && typeof attributeValue.max !== 'undefined');
    }

    /**
     * Remove selected filter from url
     *
     * @param {string} facetId
     * @param {string} facetValue
     */
    removeSelectedFilter(facetId: string, facetValue: string): void {
        facetValue = facetValue.replace(' > ', '_');
        const params = this.getSearchParams();
        const attributes = params[Constants.PARAMETER_ATTRIBUTES] as Attributes;

        if (typeof attributes[facetId] !== 'object'
            || facetId === 'price'
            || this.isRangeSliderFilter(attributes[facetId])
        ) {
            delete attributes[facetId];
        } else {
            const values = attributes[facetId];
            for (const value in values) {
                if (!Object.prototype.hasOwnProperty.call(values, value)) {
                    continue;
                }

                if (values[value] === facetValue) {
                    delete attributes[facetId][value];
                }
            }
        }

        params[Constants.PARAMETER_ATTRIBUTES] = attributes;
        delete params[Constants.PARAMETER_PAGE];

        document.location.search = '?' + $.param(params);
    }

    /**
     * Get selected filter value from url.
     */
    getSelectedFilterValue(facetId: string): Facet|null {
        const params = this.getSearchParams();

        if (!(Constants.PARAMETER_ATTRIBUTES in params)) {
            return null;
        }

        const attributes = params[Constants.PARAMETER_ATTRIBUTES] as Attributes;

        if (!(facetId in attributes)) {
            return null;
        }

        return attributes[facetId];
    }

    /**
     * Get simple url parameter value.
     */
    getUrlParamValue(key: string): string|number|object|null|undefined {
        const params = this.getSearchParams();

        if (!(key in params)) {
            return null;
        }

        return params[key];
    }

    /**
     * Get simple url parameter value.
     */
    setUrlParamValue(key: string, value: string|string[]|number|number[]) {
        const params = this.getSearchParams();

        params[key] = value;

        document.location.search = '?' + $.param(params);
    }

    /**
     * Set multiple url parameter values
     */
    setUrlParamValues(keyValueArray: UrlParameterValue[]) {
        const params = this.getSearchParams();

        keyValueArray.forEach(function (keyValueObject: UrlParameterValue) {
            params[keyValueObject.key] = keyValueObject.value;
        });

        document.location.search = '?' + $.param(params);
    }

    /**
     * Get key from object by value.
     */
    getKeyByValue(obj: Attribute, value: string): string|number {
        for (const prop in obj) {
            if (Object.prototype.hasOwnProperty.call(obj, prop)) {
                if (obj[prop] === value) {
                    return prop;
                }
            }
        }

        return -1;
    }

    /**
     * Get key from object by value suffix
     */
    getKeyBySuffix(object: Attribute, value: string): string|number {
        for (const prop in object) {
            if (Object.prototype.hasOwnProperty.call(object, prop)) {
                const val = object[prop] as string;

                if (val.endsWith(value)) {
                    return prop;
                }
            }
        }

        return -1;
    }

    /**
     * Remove all `attrib` url params and reload the page
     */
    removeAllAttribsAndRefresh() {
        const params = this.getSearchParams();
        delete params[Constants.PARAMETER_PAGE];
        delete params[Constants.PARAMETER_ATTRIBUTES];
        document.location.search = '?' + $.param(params);
    }

    getFacetIdInfoMap(store?: PlentyVuexStore): { [key: string]: Facet } {
        const map: { [key: string]: Facet } = {};

        if (!store) {
            return map;
        }

        store.state.itemList.facets.forEach(facet => {
            map[facet.id] = facet;
        });

        return map;
    }
}

export interface UrlParams {
    sorting?: string;
    [key: string]: string|number|object|undefined;
}

export interface UrlParameterValue {
    key: string;
    value: string|number;
}

type Attributes = {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    [key in keyof Attribute]: any
}

export interface Attribute {
    [key: string]: string|number|object|undefined;
}

export interface PriceFacetValue {
    min?: number;
    max?: number;
}

export default new UrlBuilder();
