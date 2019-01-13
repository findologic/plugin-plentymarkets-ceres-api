import Constants from '../constants';

export default {
    data() {
        return {
            urlParams: false
        }
    },

    methods:{
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

        getSearchParams()
        {
            if (this.urlParams) {
                return this.urlParams;
            }

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

            this.urlParams = requestParameters;

            return this.urlParams;
        },

        updateSelectedFilters(facetId, facetValue)
        {
            let params = this.getSearchParams();

            if (!(Constants.PARAMETER_ATTRIBUTES in params)) {
                params[Constants.PARAMETER_ATTRIBUTES] = {};
            }

            let attributes = params[Constants.PARAMETER_ATTRIBUTES];

            if (this.facet.id === 'price') {
                attributes[facetId] = {
                    min: facetValue.min,
                    max: facetValue.max
                };
            } else if (this.facet.select === 'single') {
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

            document.location.search = '?' + $.param(params);
        },

        isValueSelected(facetId, facetValue)
        {
            let params = this.getSearchParams();

            if (!(Constants.PARAMETER_ATTRIBUTES in params)) {
                return false;
            }

            let attributes = params[Constants.PARAMETER_ATTRIBUTES];

            if (!(facetId in attributes)) {
                return false;
            }

            if (this.facet.select === 'single' && attributes[facetId] === facetValue) {
                return true;
            }

            if (this.getKeyByValue(attributes[facetId], facetValue) !== -1) {
                return true;
            }

            return false;
        },

        getSelectedFilters()
        {
            let selectedFilters = [];
            let params = this.getSearchParams();

            if (!(Constants.PARAMETER_ATTRIBUTES in params)) {
                return selectedFilters;
            }

            for(var filter in params[Constants.PARAMETER_ATTRIBUTES]) {
                if (filter === price) {

                }

            }

            return selectedFilters;
        },

        getSearchParamValue(facetId) {
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

        getKeyByValue(object, value) {
            for(var prop in object) {
                if(object.hasOwnProperty(prop)) {
                    if(object[prop] === value)
                        return prop;
                }
            }

            return -1;
        }
    }
}