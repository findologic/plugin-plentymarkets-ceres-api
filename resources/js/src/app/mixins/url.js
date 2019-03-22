import Constants from '../constants';

export default {
    methods:{
        /*
         * Plentymarkets standart method for parsing params from string into object
         *
         * @param {string} urlParams
         * @returns {Object}
         */
        getUrlParams(urlParams) {
            if (urlParams) {
                var tokens;
                var params = {};
                var regex = /[?&]?([^=]+)=([^&]*)/g;

                urlParams = urlParams.split("+").join(" ");

                // eslint-disable-next-line
                while (tokens = regex.exec(urlParams)) {
                    params[decodeURIComponent(tokens[1])] = decodeURIComponent(tokens[2]);
                }

                return params;
            }

            return {};
        },

        /*
         * FINDOLOGIC method for parsing url params into a parameter map from current url
         * Taken from direct integration flUtils class
         *
         * @returns {{}} The parameter map
         */
        getSearchParams() {
            var queryString = document.location.search;
            var requestParameters = {};

            /*
             * Remove any leading ? as it is not part of the query string but will be passed due to the way we use
             * parseQueryString
             */
            if (typeof queryString !== 'undefined') {
                queryString = queryString.replace(/^\?/, "");
            } else {
                queryString = '';
            }

            var strArr = String(queryString)
                    .replace(/^&/, '')
                    .replace(/&$/, '')
                    .split('&'),
                sal = strArr.length,
                i, j, ct, p, lastObj, obj, lastIter, undef, chr, tmp, key, value,
                postLeftBracketPos, keys, keysLen,
                fixStr = function(queryString) {
                    return decodeURIComponent(queryString.replace(/\+/g, '%20'));
                };

            for (i = 0; i < sal; i++) {
                tmp = strArr[i].split('=');
                key = fixStr(tmp[0]);
                value = (tmp.length < 2) ? '' : fixStr(tmp[1]);

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
                        lastIter = j !== keys.length - 1;
                        lastObj = obj;
                        if ((key !== '' && key !== ' ') || j === 0) {
                            if (obj[key] === undef) {
                                obj[key] = {};
                            }
                            obj = obj[key];
                        } else { // To insert new dimension
                            ct = -1;
                            for (p in obj) {
                                if (obj.hasOwnProperty(p)) {
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
        },

        /*
         * Update url with selected filters
         *
         * @param {string} facetId
         * @param {string|array} facetValue
         */
        updateSelectedFilters(facetId, facetValue) {
            let params = this.getSearchParams();

            if (!(Constants.PARAMETER_ATTRIBUTES in params)) {
                params[Constants.PARAMETER_ATTRIBUTES] = {};
            }

            let attributes = params[Constants.PARAMETER_ATTRIBUTES];

            if (facetId === 'price') {
                attributes[facetId] = {
                    min: facetValue.min,
                    max: facetValue.max
                };
            } else if (this.facet.select === 'single' && facetId !== 'cat') {
                if (facetId in attributes) {
                    if (attributes[facetId] === facetValue) {
                        delete attributes[facetId];
                    } else {
                        attributes[facetId] = facetValue;
                    }
                } else {
                    attributes[facetId] = facetValue;
                }
            } else {
                if (!(facetId in attributes)) {
                    attributes[facetId] = [facetValue];
                } else {
                    let valueId = this.getKeyByValue(attributes[facetId], facetValue);

                    if (valueId === -1) {
                        let index = Object.keys(attributes[facetId]).length;
                        attributes[facetId][index] = facetValue;
                    } else {
                        delete attributes[facetId][valueId];
                    }
                }
            }

            params[Constants.PARAMETER_ATTRIBUTES] = attributes;
            params[Constants.PARAMETER_PAGE] = 1;

            document.location.search = '?' + $.param(params);
        },

        /*
         * Check if value is selected
         *
         * @param {string} facetId
         * @param {string} facetValue
         * @returns {boolean}
         */
        isValueSelected(facetId, facetValue) {
            let params = this.getSearchParams();

            if (!(Constants.PARAMETER_ATTRIBUTES in params)) {
                return false;
            }

            let attributes = params[Constants.PARAMETER_ATTRIBUTES];

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
        },

        /*
         * Get the list of selected filters from url
         *
         * @returns {Object}
         */
        getSelectedFilters() {
            let selectedFilters = [];
            let params = this.getSearchParams();

            if (!(Constants.PARAMETER_ATTRIBUTES in params)) {
                return selectedFilters;
            }

            let attributes = params[Constants.PARAMETER_ATTRIBUTES];

            for (var filter in attributes) {
                if (filter === 'price') {
                    selectedFilters.push({
                        id: 'price',
                        name: attributes[filter].min + ' - ' + attributes[filter].max
                    });

                    continue;
                }

                if (typeof attributes[filter] === 'object') {
                    let values = attributes[filter];
                    for (var value in values) {
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
        },

        /*
         * Remove selected filter from url
         *
         * @param {string} facetId
         * @param {string} facetValue
         */
        removeSelectedFilter(facetId, facetValue) {
            facetValue = facetValue.replace(' > ', '_');
            let params = this.getSearchParams();
            let attributes = params[Constants.PARAMETER_ATTRIBUTES];

            if (typeof attributes[facetId] !== 'object' || facetId === 'price') {
                delete attributes[facetId];
            } else {
                var values = attributes[facetId];
                for (var value in values) {
                    if (values[value] === facetValue) {
                        delete attributes[facetId][value];
                    }
                }
            }

            params[Constants.PARAMETER_ATTRIBUTES] = attributes;
            params[Constants.PARAMETER_PAGE] = 1;

            document.location.search = '?' + $.param(params);
        },

        /*
         * Get selected filter value from url
         *
         * @param {string} facetId
         * @returns {Object|null}
         */
        getSelectedFilterValue(facetId) {
            let params = this.getSearchParams();

            if (!(Constants.PARAMETER_ATTRIBUTES in params)) {
                return null;
            }

            let attributes = params[Constants.PARAMETER_ATTRIBUTES];

            if (!(facetId in attributes)) {
                return null;
            }

            return attributes[facetId];
        },

        /*
         * Get simple url parameter value
         *
         * @param {string} key
         * @returns {string|null}
         */
        getUrlParamValue(key) {
            let params = this.getSearchParams();

            if (!(key in params)) {
                return null;
            }

            return params[key];
        },

        /*
         * Get simple url parameter value
         *
         * @param {string} key
         * @param {string|array} value
         */
        setUrlParamValue(key, value) {
            let params = this.getSearchParams();

            params[key] = value;

            document.location.search = '?' + $.param(params);
        },

        /**
         * Set multiple url parameter values
         *
         * @param {array} keyValueArray
         */
        setUrlParamValues(keyValueArray) {
            let params = this.getSearchParams();

            keyValueArray.forEach(function (keyValueObject) {
                params[keyValueObject.key] = keyValueObject.value;
            });

            document.location.search = '?' + $.param(params);
        },

        /*
         * Get key from object by value
         *
         * @param {Object} object
         * @param {string} value
         * @returns {string|number}
         */
        getKeyByValue(object, value) {
            for (var prop in object) {
                if (object.hasOwnProperty(prop)) {
                    if (object[prop] === value) {
                        return prop;
                    }
                }
            }

            return -1;
        },

        /*
         * Get key from object by value suffix
         *
         * @param {Object} object
         * @param {string} value
         * @returns {string|number}
         */
        getKeyBySuffix(object, value) {
            for (var prop in object) {
                if (object.hasOwnProperty(prop)) {
                    if (object[prop].endsWith(value)) {
                        return prop;
                    }
                }
            }

            return -1;
        },

        /**
         *  Remove all `attrib` url params and reload the page
         */
        removeAllAttribsAndRefresh() {
            let params = this.getSearchParams();
            params[Constants.PARAMETER_PAGE] = 1;
            delete params[Constants.PARAMETER_ATTRIBUTES];
            document.location.search = '?' + $.param(params);
        }
    }
}