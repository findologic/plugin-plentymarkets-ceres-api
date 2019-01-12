import Constants from '../constants';

export default {
    methods:{
        getUrlParams(queryString)
        {
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

            return requestParameters;
        },

        updateSelectedFilters(facetId, facetValue)
        {
            var params = this.getUrlParams(document.location.search);
            const value = facetValue.name;

            console.log('url params');
            console.log(params);

            if (!(Constants.PARAMETER_ATTRIBUTES in params)) {
                params[Constants.PARAMETER_ATTRIBUTES] = {};
            }

            let attributes = params[Constants.PARAMETER_ATTRIBUTES];

            if (this.facet.select === 'single') {
                if (facetId in attributes) {
                    if (attributes[facetId] === value) {
                        delete attributes[facetId];
                    } else {
                        attributes[facetId] = value;
                    }
                } else {
                    attributes[facetId] = value;
                }
            } else {
                if (!(facetId in attributes)) {
                    attributes[facetId] = [value];
                } else if ($.inArray(value, attributes[facetId]) !== -1) {
                    attributes[facetId].push(value);
                } else {
                    attributes[facetId] = attributes[facetId].filter(function(selectedValue) { return selectedValue !== value });
                }
            }

            params[Constants.PARAMETER_ATTRIBUTES] = attributes;

            console.log('updated url params');
            console.log(params);
            console.log($.param(params));
            document.location.search = '?' + $.param(params);
        }
    }
}