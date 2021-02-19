// @ts-nocheck
import Constants from '../constants';
import Component from 'vue-class-component';
import { Vue } from 'vue-property-decorator';

@Component
export default class Url extends Vue {
    /**
     * Plentymarkets standart method for parsing params from string into object
     *
     * @public
     * @param {string} urlParams
     * @returns {Object}
     */
    getUrlParams(urlParams): UrlParams {
        if (urlParams) {
            let tokens;
            const params = {};
            const regex = /[?&]?([^=]+)=([^&]*)/g;

            urlParams = urlParams.split("+").join(" ");

            // eslint-disable-next-line
            while (tokens = regex.exec(urlParams)) {
                params[decodeURIComponent(tokens[1])] = decodeURIComponent(tokens[2]);
            }

            return params;
        }

        return {};
    }

    /**
     * Findologic method for parsing url params into a parameter map from current url
     * Taken from direct integration flUtils class
     *
     * @returns {{}} The parameter map
     */
    getSearchParams() {
        let queryString = document.location.search;
        const requestParameters = {};

        /*
         * Remove any leading ? as it is not part of the query string but will be passed due to the way we use
         * parseQueryString
         */
        if (typeof queryString !== 'undefined') {
            queryString = queryString.replace(/^\?/, "");
        } else {
            queryString = '';
        }

        const strArr = String(queryString)
            .replace(/^&/, '')
            .replace(/&$/, '')
            .split('&');

        const sal = strArr.length;
        const fixStr = function(queryString) {
            return decodeURIComponent(queryString.replace(/\+/g, '%20'));
        }

        let i, j, ct, p, lastObj, obj, undef, chr, tmp, key, value,
          postLeftBracketPos, keys, keysLen;

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

                obj = requestParameters;
                for (j = 0, keysLen = keys.length; j < keysLen; j++) {
                    key = keys[j].replace(/^['"]/, '')
                      .replace(/['"]$/, '');
                    lastObj = obj;
                    if ((key !== '' && key !== ' ') || j === 0) {
                        if (obj[key] === undef) {
                            obj[key] = {};
                        }
                        obj = obj[key];
                    } else { // To insert new dimension
                        ct = -1;
                        for (p in obj) {
                            if (obj.prototype.hasOwnProperty.call(p)) {
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
     *
     * @param {string} facetId
     * @param {string|array} facetValue
     */
    updateSelectedFilters(facetId, facetValue) {
        const params = this.getSearchParams();

        if (!(Constants.PARAMETER_ATTRIBUTES in params)) {
            params[Constants.PARAMETER_ATTRIBUTES] = {};
        }

        const attributes = params[Constants.PARAMETER_ATTRIBUTES];

        if (facetId === 'price' || this.facet.findologicFilterType === 'range-slider') {
            attributes[facetId] = {
                min: facetValue.min,
                max: facetValue.max
            };
        } else if (this.facet.select === 'single') {
            if (attributes[facetId] && Object.values(attributes[facetId]).includes(facetValue)) {
                if (this.facet.id === 'cat' && facetValue.includes('_') ) {
                    // Subcategory deselection
                    attributes[facetId] = [facetValue.split('_')[0]];
                } else {
                    const index = Object.values(attributes[facetId]).indexOf(facetValue);
                    delete attributes[facetId][index];
                }
            } else {
                attributes[facetId] = [facetValue];
            }
        } else {
            if (!(facetId in attributes)) {
                attributes[facetId] = [facetValue];
            } else {
                const valueId = this.getKeyByValue(attributes[facetId], facetValue);

                if (valueId === -1) {
                    const index = Object.keys(attributes[facetId]).length;
                    attributes[facetId][index] = facetValue;
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
     *
     * @param {string} facetId
     * @param {string} facetValue
     * @returns {boolean}
     */
    isValueSelected(facetId, facetValue) {
        const params = this.getSearchParams();

        if (!(Constants.PARAMETER_ATTRIBUTES in params)) {
            return false;
        }

        const attributes = params[Constants.PARAMETER_ATTRIBUTES];

        if (!(facetId in attributes)) {
            return false;
        } else if (facetId !== 'cat' && this.facet.select === 'single' && attributes[facetId] === facetValue) {
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
     * Get the list of selected filters from url
     *
     * @returns {Object}
     */
    getSelectedFilters() {
        const selectedFilters = [];
        const params = this.getSearchParams();

        if (!(Constants.PARAMETER_ATTRIBUTES in params)) {
            return selectedFilters;
        }

        const attributes = params[Constants.PARAMETER_ATTRIBUTES];

        for (const filter in attributes) {
            if (filter === 'wizard') {
                continue;
            }

            if (filter === 'price' || this.isRangeSliderFilter(attributes[filter])) {
                selectedFilters.push({
                    id: filter,
                    name: attributes[filter].min + ' - ' + attributes[filter].max
                });

                continue;
            }

            if (typeof attributes[filter] === 'object') {
                const values = attributes[filter];
                for (const value in values) {
                    selectedFilters.push({
                        id: filter,
                        name: values[value].replace(/_/g, " > ")
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

    /**
     * @param attributeValue
     * @returns {boolean}
     */
    isRangeSliderFilter(attributeValue) {
        return (typeof attributeValue.min !== 'undefined' && typeof attributeValue.max !== 'undefined')
    }

    /**
     * Remove selected filter from url
     *
     * @param {string} facetId
     * @param {string} facetValue
     */
    removeSelectedFilter(facetId, facetValue) {
        facetValue = facetValue.replace(' > ', '_');
        const params = this.getSearchParams();
        const attributes = params[Constants.PARAMETER_ATTRIBUTES];

        if (typeof attributes[facetId] !== 'object'
          || facetId === 'price'
          || this.isRangeSliderFilter(attributes[facetId])
        ) {
            delete attributes[facetId];
        } else {
            const values = attributes[facetId];
            for (const value in values) {
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
     * Get selected filter value from url
     *
     * @param {string} facetId
     * @returns {Object|null}
     */
    getSelectedFilterValue(facetId) {
        const params = this.getSearchParams();

        if (!(Constants.PARAMETER_ATTRIBUTES in params)) {
            return null;
        }

        const attributes = params[Constants.PARAMETER_ATTRIBUTES];

        if (!(facetId in attributes)) {
            return null;
        }

        return attributes[facetId];
    }

    /**
     * Get simple url parameter value
     *
     * @param {string} key
     * @returns {string|null}
     */
    getUrlParamValue(key) {
        const params = this.getSearchParams();

        if (!(key in params)) {
            return null;
        }

        return params[key];
    }

    /**
     * Get simple url parameter value
     *
     * @param {string} key
     * @param {string|array} value
     */
    setUrlParamValue(key, value) {
        const params = this.getSearchParams();

        params[key] = value;

        document.location.search = '?' + $.param(params);
    }

    /**
     * Set multiple url parameter values
     *
     * @public
     * @param {array} keyValueArray
     */
    setUrlParamValues(keyValueArray) {
        const params = this.getSearchParams();

        keyValueArray.forEach(function (keyValueObject) {
            params[keyValueObject.key] = keyValueObject.value;
        });

        document.location.search = '?' + $.param(params);
    }

    /**
     * Get key from object by value
     *
     * @param {Object} object
     * @param {string} value
     * @returns {string|number}
     */
    getKeyByValue(object, value) {
        for (const prop in object) {
            if (object.prototype.hasOwnProperty.call(prop)) {
                if (object[prop] === value) {
                    return prop;
                }
            }
        }

        return -1;
    }

    /**
     * Get key from object by value suffix
     *
     * @param {Object} object
     * @param {string} value
     * @returns {string|number}
     */
    getKeyBySuffix(object, value) {
        for (const prop in object) {
            if (object.prototype.hasOwnProperty.call(prop)) {
                if (object[prop].endsWith(value)) {
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
}

interface UrlParams {
    sorting: undefined|null|string;
}
