(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
(function (process){(function (){
'use strict'

if (process.env.NODE_ENV === 'production') {
  module.exports = require('./svg-injector.cjs.production.js')
} else {
  module.exports = require('./svg-injector.cjs.development.js')
}

}).call(this)}).call(this,require('_process'))

},{"./svg-injector.cjs.development.js":2,"./svg-injector.cjs.production.js":3,"_process":6}],2:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, '__esModule', { value: true });

var tslib = require('tslib');
var contentType = require('content-type');

var cache = new Map();

var cloneSvg = function cloneSvg(sourceSvg) {
  return sourceSvg.cloneNode(true);
};

var isLocal = function isLocal() {
  return window.location.protocol === 'file:';
};

var makeAjaxRequest = function makeAjaxRequest(url, httpRequestWithCredentials, callback) {
  var httpRequest = new XMLHttpRequest();

  httpRequest.onreadystatechange = function () {
    try {
      if (!/\.svg/i.test(url) && httpRequest.readyState === 2) {
        var contentType$1 = httpRequest.getResponseHeader('Content-Type');

        if (!contentType$1) {
          throw new Error('Content type not found');
        }

        var type = contentType.parse(contentType$1).type;

        if (!(type === 'image/svg+xml' || type === 'text/plain')) {
          throw new Error("Invalid content type: ".concat(type));
        }
      }

      if (httpRequest.readyState === 4) {
        if (httpRequest.status === 404 || httpRequest.responseXML === null) {
          throw new Error(isLocal() ? 'Note: SVG injection ajax calls do not work locally without ' + 'adjusting security settings in your browser. Or consider ' + 'using a local webserver.' : 'Unable to load SVG file: ' + url);
        }

        if (httpRequest.status === 200 || isLocal() && httpRequest.status === 0) {
          callback(null, httpRequest);
        } else {
          throw new Error('There was a problem injecting the SVG: ' + httpRequest.status + ' ' + httpRequest.statusText);
        }
      }
    } catch (error) {
      httpRequest.abort();

      if (error instanceof Error) {
        callback(error, httpRequest);
      } else {
        throw error;
      }
    }
  };

  httpRequest.open('GET', url);
  httpRequest.withCredentials = httpRequestWithCredentials;

  if (httpRequest.overrideMimeType) {
    httpRequest.overrideMimeType('text/xml');
  }

  httpRequest.send();
};

var requestQueue = {};
var queueRequest = function queueRequest(url, callback) {
  requestQueue[url] = requestQueue[url] || [];
  requestQueue[url].push(callback);
};
var processRequestQueue = function processRequestQueue(url) {
  var _loop_1 = function _loop_1(i, len) {
    setTimeout(function () {
      if (Array.isArray(requestQueue[url])) {
        var cacheValue = cache.get(url);
        var callback = requestQueue[url][i];

        if (cacheValue instanceof SVGSVGElement) {
          callback(null, cloneSvg(cacheValue));
        }

        if (cacheValue instanceof Error) {
          callback(cacheValue);
        }

        if (i === requestQueue[url].length - 1) {
          delete requestQueue[url];
        }
      }
    }, 0);
  };

  for (var i = 0, len = requestQueue[url].length; i < len; i++) {
    _loop_1(i);
  }
};

var loadSvgCached = function loadSvgCached(url, httpRequestWithCredentials, callback) {
  if (cache.has(url)) {
    var cacheValue = cache.get(url);

    if (cacheValue === undefined) {
      queueRequest(url, callback);
      return;
    }

    if (cacheValue instanceof SVGSVGElement) {
      callback(null, cloneSvg(cacheValue));
      return;
    }
  }

  cache.set(url, undefined);
  queueRequest(url, callback);
  makeAjaxRequest(url, httpRequestWithCredentials, function (error, httpRequest) {
    if (error) {
      cache.set(url, error);
    } else if (httpRequest.responseXML instanceof Document && httpRequest.responseXML.documentElement && httpRequest.responseXML.documentElement instanceof SVGSVGElement) {
      cache.set(url, httpRequest.responseXML.documentElement);
    }

    processRequestQueue(url);
  });
};

var loadSvgUncached = function loadSvgUncached(url, httpRequestWithCredentials, callback) {
  makeAjaxRequest(url, httpRequestWithCredentials, function (error, httpRequest) {
    if (error) {
      callback(error);
    } else if (httpRequest.responseXML instanceof Document && httpRequest.responseXML.documentElement && httpRequest.responseXML.documentElement instanceof SVGSVGElement) {
      callback(null, httpRequest.responseXML.documentElement);
    }
  });
};

var idCounter = 0;

var uniqueId = function uniqueId() {
  return ++idCounter;
};

var injectedElements = [];
var ranScripts = {};
var svgNamespace = 'http://www.w3.org/2000/svg';
var xlinkNamespace = 'http://www.w3.org/1999/xlink';

var injectElement = function injectElement(el, evalScripts, renumerateIRIElements, cacheRequests, httpRequestWithCredentials, beforeEach, callback) {
  var elUrl = el.getAttribute('data-src') || el.getAttribute('src');

  if (!elUrl) {
    callback(new Error('Invalid data-src or src attribute'));
    return;
  }

  if (injectedElements.indexOf(el) !== -1) {
    injectedElements.splice(injectedElements.indexOf(el), 1);
    el = null;
    return;
  }

  injectedElements.push(el);
  el.setAttribute('src', '');
  var loadSvg = cacheRequests ? loadSvgCached : loadSvgUncached;
  loadSvg(elUrl, httpRequestWithCredentials, function (error, svg) {
    if (!svg) {
      injectedElements.splice(injectedElements.indexOf(el), 1);
      el = null;
      callback(error);
      return;
    }

    var elId = el.getAttribute('id');

    if (elId) {
      svg.setAttribute('id', elId);
    }

    var elTitle = el.getAttribute('title');

    if (elTitle) {
      svg.setAttribute('title', elTitle);
    }

    var elWidth = el.getAttribute('width');

    if (elWidth) {
      svg.setAttribute('width', elWidth);
    }

    var elHeight = el.getAttribute('height');

    if (elHeight) {
      svg.setAttribute('height', elHeight);
    }

    var mergedClasses = Array.from(new Set(tslib.__spreadArray(tslib.__spreadArray(tslib.__spreadArray([], (svg.getAttribute('class') || '').split(' '), true), ['injected-svg'], false), (el.getAttribute('class') || '').split(' '), true))).join(' ').trim();
    svg.setAttribute('class', mergedClasses);
    var elStyle = el.getAttribute('style');

    if (elStyle) {
      svg.setAttribute('style', elStyle);
    }

    svg.setAttribute('data-src', elUrl);
    var elData = [].filter.call(el.attributes, function (at) {
      return /^data-\w[\w-]*$/.test(at.name);
    });
    Array.prototype.forEach.call(elData, function (dataAttr) {
      if (dataAttr.name && dataAttr.value) {
        svg.setAttribute(dataAttr.name, dataAttr.value);
      }
    });

    if (renumerateIRIElements) {
      var iriElementsAndProperties_1 = {
        clipPath: ['clip-path'],
        'color-profile': ['color-profile'],
        cursor: ['cursor'],
        filter: ['filter'],
        linearGradient: ['fill', 'stroke'],
        marker: ['marker', 'marker-start', 'marker-mid', 'marker-end'],
        mask: ['mask'],
        path: [],
        pattern: ['fill', 'stroke'],
        radialGradient: ['fill', 'stroke']
      };
      var element_1;
      var elements_1;
      var properties_1;
      var currentId_1;
      var newId_1;
      Object.keys(iriElementsAndProperties_1).forEach(function (key) {
        element_1 = key;
        properties_1 = iriElementsAndProperties_1[key];
        elements_1 = svg.querySelectorAll(element_1 + '[id]');

        var _loop_1 = function _loop_1(a, elementsLen) {
          currentId_1 = elements_1[a].id;
          newId_1 = currentId_1 + '-' + uniqueId();
          var referencingElements;
          Array.prototype.forEach.call(properties_1, function (property) {
            referencingElements = svg.querySelectorAll('[' + property + '*="' + currentId_1 + '"]');

            for (var b = 0, referencingElementLen = referencingElements.length; b < referencingElementLen; b++) {
              var attrValue = referencingElements[b].getAttribute(property);

              if (attrValue && !attrValue.match(new RegExp('url\\("?#' + currentId_1 + '"?\\)'))) {
                continue;
              }

              referencingElements[b].setAttribute(property, 'url(#' + newId_1 + ')');
            }
          });
          var allLinks = svg.querySelectorAll('[*|href]');
          var links = [];

          for (var c = 0, allLinksLen = allLinks.length; c < allLinksLen; c++) {
            var href = allLinks[c].getAttributeNS(xlinkNamespace, 'href');

            if (href && href.toString() === '#' + elements_1[a].id) {
              links.push(allLinks[c]);
            }
          }

          for (var d = 0, linksLen = links.length; d < linksLen; d++) {
            links[d].setAttributeNS(xlinkNamespace, 'href', '#' + newId_1);
          }

          elements_1[a].id = newId_1;
        };

        for (var a = 0, elementsLen = elements_1.length; a < elementsLen; a++) {
          _loop_1(a);
        }
      });
    }

    svg.removeAttribute('xmlns:a');
    var scripts = svg.querySelectorAll('script');
    var scriptsToEval = [];
    var script;
    var scriptType;

    for (var i = 0, scriptsLen = scripts.length; i < scriptsLen; i++) {
      scriptType = scripts[i].getAttribute('type');

      if (!scriptType || scriptType === 'application/ecmascript' || scriptType === 'application/javascript' || scriptType === 'text/javascript') {
        script = scripts[i].innerText || scripts[i].textContent;

        if (script) {
          scriptsToEval.push(script);
        }

        svg.removeChild(scripts[i]);
      }
    }

    if (scriptsToEval.length > 0 && (evalScripts === 'always' || evalScripts === 'once' && !ranScripts[elUrl])) {
      for (var l = 0, scriptsToEvalLen = scriptsToEval.length; l < scriptsToEvalLen; l++) {
        new Function(scriptsToEval[l])(window);
      }

      ranScripts[elUrl] = true;
    }

    var styleTags = svg.querySelectorAll('style');
    Array.prototype.forEach.call(styleTags, function (styleTag) {
      styleTag.textContent += '';
    });
    svg.setAttribute('xmlns', svgNamespace);
    svg.setAttribute('xmlns:xlink', xlinkNamespace);
    beforeEach(svg);

    if (!el.parentNode) {
      injectedElements.splice(injectedElements.indexOf(el), 1);
      el = null;
      callback(new Error('Parent node is null'));
      return;
    }

    el.parentNode.replaceChild(svg, el);
    injectedElements.splice(injectedElements.indexOf(el), 1);
    el = null;
    callback(null, svg);
  });
};

var SVGInjector = function SVGInjector(elements, _a) {
  var _b = _a === void 0 ? {} : _a,
      _c = _b.afterAll,
      afterAll = _c === void 0 ? function () {
    return undefined;
  } : _c,
      _d = _b.afterEach,
      afterEach = _d === void 0 ? function () {
    return undefined;
  } : _d,
      _e = _b.beforeEach,
      beforeEach = _e === void 0 ? function () {
    return undefined;
  } : _e,
      _f = _b.cacheRequests,
      cacheRequests = _f === void 0 ? true : _f,
      _g = _b.evalScripts,
      evalScripts = _g === void 0 ? 'never' : _g,
      _h = _b.httpRequestWithCredentials,
      httpRequestWithCredentials = _h === void 0 ? false : _h,
      _j = _b.renumerateIRIElements,
      renumerateIRIElements = _j === void 0 ? true : _j;

  if (elements && 'length' in elements) {
    var elementsLoaded_1 = 0;

    for (var i = 0, j = elements.length; i < j; i++) {
      injectElement(elements[i], evalScripts, renumerateIRIElements, cacheRequests, httpRequestWithCredentials, beforeEach, function (error, svg) {
        afterEach(error, svg);

        if (elements && 'length' in elements && elements.length === ++elementsLoaded_1) {
          afterAll(elementsLoaded_1);
        }
      });
    }
  } else if (elements) {
    injectElement(elements, evalScripts, renumerateIRIElements, cacheRequests, httpRequestWithCredentials, beforeEach, function (error, svg) {
      afterEach(error, svg);
      afterAll(1);
      elements = null;
    });
  } else {
    afterAll(0);
  }
};

exports.SVGInjector = SVGInjector;


},{"content-type":4,"tslib":7}],3:[function(require,module,exports){
"use strict";Object.defineProperty(exports,"__esModule",{value:!0});var tslib=require("tslib"),contentType=require("content-type"),cache=new Map,cloneSvg=function(e){return e.cloneNode(!0)},isLocal=function(){return"file:"===window.location.protocol},makeAjaxRequest=function(e,t,r){var n=new XMLHttpRequest;n.onreadystatechange=function(){try{if(!/\.svg/i.test(e)&&2===n.readyState){var t=n.getResponseHeader("Content-Type");if(!t)throw new Error("Content type not found");var i=contentType.parse(t).type;if("image/svg+xml"!==i&&"text/plain"!==i)throw new Error("Invalid content type: ".concat(i))}if(4===n.readyState){if(404===n.status||null===n.responseXML)throw new Error(isLocal()?"Note: SVG injection ajax calls do not work locally without adjusting security settings in your browser. Or consider using a local webserver.":"Unable to load SVG file: "+e);if(!(200===n.status||isLocal()&&0===n.status))throw new Error("There was a problem injecting the SVG: "+n.status+" "+n.statusText);r(null,n)}}catch(e){if(n.abort(),!(e instanceof Error))throw e;r(e,n)}},n.open("GET",e),n.withCredentials=t,n.overrideMimeType&&n.overrideMimeType("text/xml"),n.send()},requestQueue={},queueRequest=function(e,t){requestQueue[e]=requestQueue[e]||[],requestQueue[e].push(t)},processRequestQueue=function(e){for(var t=function(t,r){setTimeout((function(){if(Array.isArray(requestQueue[e])){var r=cache.get(e),n=requestQueue[e][t];r instanceof SVGSVGElement&&n(null,cloneSvg(r)),r instanceof Error&&n(r),t===requestQueue[e].length-1&&delete requestQueue[e]}}),0)},r=0,n=requestQueue[e].length;r<n;r++)t(r)},loadSvgCached=function(e,t,r){if(cache.has(e)){var n=cache.get(e);if(void 0===n)return void queueRequest(e,r);if(n instanceof SVGSVGElement)return void r(null,cloneSvg(n))}cache.set(e,void 0),queueRequest(e,r),makeAjaxRequest(e,t,(function(t,r){t?cache.set(e,t):r.responseXML instanceof Document&&r.responseXML.documentElement&&r.responseXML.documentElement instanceof SVGSVGElement&&cache.set(e,r.responseXML.documentElement),processRequestQueue(e)}))},loadSvgUncached=function(e,t,r){makeAjaxRequest(e,t,(function(e,t){e?r(e):t.responseXML instanceof Document&&t.responseXML.documentElement&&t.responseXML.documentElement instanceof SVGSVGElement&&r(null,t.responseXML.documentElement)}))},idCounter=0,uniqueId=function(){return++idCounter},injectedElements=[],ranScripts={},svgNamespace="http://www.w3.org/2000/svg",xlinkNamespace="http://www.w3.org/1999/xlink",injectElement=function(e,t,r,n,i,a,o){var s=e.getAttribute("data-src")||e.getAttribute("src");if(s){if(-1!==injectedElements.indexOf(e))return injectedElements.splice(injectedElements.indexOf(e),1),void(e=null);injectedElements.push(e),e.setAttribute("src",""),(n?loadSvgCached:loadSvgUncached)(s,i,(function(n,i){if(!i)return injectedElements.splice(injectedElements.indexOf(e),1),e=null,void o(n);var l=e.getAttribute("id");l&&i.setAttribute("id",l);var u=e.getAttribute("title");u&&i.setAttribute("title",u);var c=e.getAttribute("width");c&&i.setAttribute("width",c);var d=e.getAttribute("height");d&&i.setAttribute("height",d);var f=Array.from(new Set(tslib.__spreadArray(tslib.__spreadArray(tslib.__spreadArray([],(i.getAttribute("class")||"").split(" "),!0),["injected-svg"],!1),(e.getAttribute("class")||"").split(" "),!0))).join(" ").trim();i.setAttribute("class",f);var p=e.getAttribute("style");p&&i.setAttribute("style",p),i.setAttribute("data-src",s);var m=[].filter.call(e.attributes,(function(e){return/^data-\w[\w-]*$/.test(e.name)}));if(Array.prototype.forEach.call(m,(function(e){e.name&&e.value&&i.setAttribute(e.name,e.value)})),r){var v,h,g,A,b={clipPath:["clip-path"],"color-profile":["color-profile"],cursor:["cursor"],filter:["filter"],linearGradient:["fill","stroke"],marker:["marker","marker-start","marker-mid","marker-end"],mask:["mask"],path:[],pattern:["fill","stroke"],radialGradient:["fill","stroke"]};Object.keys(b).forEach((function(e){h=b[e];for(var t=function(e,t){var r;A=(g=v[e].id)+"-"+uniqueId(),Array.prototype.forEach.call(h,(function(e){for(var t=0,n=(r=i.querySelectorAll("["+e+'*="'+g+'"]')).length;t<n;t++){var a=r[t].getAttribute(e);a&&!a.match(new RegExp('url\\("?#'+g+'"?\\)'))||r[t].setAttribute(e,"url(#"+A+")")}}));for(var n=i.querySelectorAll("[*|href]"),a=[],o=0,s=n.length;o<s;o++){var l=n[o].getAttributeNS(xlinkNamespace,"href");l&&l.toString()==="#"+v[e].id&&a.push(n[o])}for(var u=0,c=a.length;u<c;u++)a[u].setAttributeNS(xlinkNamespace,"href","#"+A);v[e].id=A},r=0,n=(v=i.querySelectorAll(e+"[id]")).length;r<n;r++)t(r)}))}i.removeAttribute("xmlns:a");for(var E,y,w=i.querySelectorAll("script"),S=[],q=0,j=w.length;q<j;q++)(y=w[q].getAttribute("type"))&&"application/ecmascript"!==y&&"application/javascript"!==y&&"text/javascript"!==y||((E=w[q].innerText||w[q].textContent)&&S.push(E),i.removeChild(w[q]));if(S.length>0&&("always"===t||"once"===t&&!ranScripts[s])){for(var x=0,k=S.length;x<k;x++)new Function(S[x])(window);ranScripts[s]=!0}var G=i.querySelectorAll("style");if(Array.prototype.forEach.call(G,(function(e){e.textContent+=""})),i.setAttribute("xmlns",svgNamespace),i.setAttribute("xmlns:xlink",xlinkNamespace),a(i),!e.parentNode)return injectedElements.splice(injectedElements.indexOf(e),1),e=null,void o(new Error("Parent node is null"));e.parentNode.replaceChild(i,e),injectedElements.splice(injectedElements.indexOf(e),1),e=null,o(null,i)}))}else o(new Error("Invalid data-src or src attribute"))},SVGInjector=function(e,t){var r=void 0===t?{}:t,n=r.afterAll,i=void 0===n?function(){}:n,a=r.afterEach,o=void 0===a?function(){}:a,s=r.beforeEach,l=void 0===s?function(){}:s,u=r.cacheRequests,c=void 0===u||u,d=r.evalScripts,f=void 0===d?"never":d,p=r.httpRequestWithCredentials,m=void 0!==p&&p,v=r.renumerateIRIElements,h=void 0===v||v;if(e&&"length"in e)for(var g=0,A=0,b=e.length;A<b;A++)injectElement(e[A],f,h,c,m,l,(function(t,r){o(t,r),e&&"length"in e&&e.length===++g&&i(g)}));else e?injectElement(e,f,h,c,m,l,(function(t,r){o(t,r),i(1),e=null})):i(0)};exports.SVGInjector=SVGInjector;


},{"content-type":4,"tslib":7}],4:[function(require,module,exports){
/*!
 * content-type
 * Copyright(c) 2015 Douglas Christopher Wilson
 * MIT Licensed
 */

'use strict'

/**
 * RegExp to match *( ";" parameter ) in RFC 7231 sec 3.1.1.1
 *
 * parameter     = token "=" ( token / quoted-string )
 * token         = 1*tchar
 * tchar         = "!" / "#" / "$" / "%" / "&" / "'" / "*"
 *               / "+" / "-" / "." / "^" / "_" / "`" / "|" / "~"
 *               / DIGIT / ALPHA
 *               ; any VCHAR, except delimiters
 * quoted-string = DQUOTE *( qdtext / quoted-pair ) DQUOTE
 * qdtext        = HTAB / SP / %x21 / %x23-5B / %x5D-7E / obs-text
 * obs-text      = %x80-FF
 * quoted-pair   = "\" ( HTAB / SP / VCHAR / obs-text )
 */
var PARAM_REGEXP = /; *([!#$%&'*+.^_`|~0-9A-Za-z-]+) *= *("(?:[\u000b\u0020\u0021\u0023-\u005b\u005d-\u007e\u0080-\u00ff]|\\[\u000b\u0020-\u00ff])*"|[!#$%&'*+.^_`|~0-9A-Za-z-]+) */g
var TEXT_REGEXP = /^[\u000b\u0020-\u007e\u0080-\u00ff]+$/
var TOKEN_REGEXP = /^[!#$%&'*+.^_`|~0-9A-Za-z-]+$/

/**
 * RegExp to match quoted-pair in RFC 7230 sec 3.2.6
 *
 * quoted-pair = "\" ( HTAB / SP / VCHAR / obs-text )
 * obs-text    = %x80-FF
 */
var QESC_REGEXP = /\\([\u000b\u0020-\u00ff])/g

/**
 * RegExp to match chars that must be quoted-pair in RFC 7230 sec 3.2.6
 */
var QUOTE_REGEXP = /([\\"])/g

/**
 * RegExp to match type in RFC 7231 sec 3.1.1.1
 *
 * media-type = type "/" subtype
 * type       = token
 * subtype    = token
 */
var TYPE_REGEXP = /^[!#$%&'*+.^_`|~0-9A-Za-z-]+\/[!#$%&'*+.^_`|~0-9A-Za-z-]+$/

/**
 * Module exports.
 * @public
 */

exports.format = format
exports.parse = parse

/**
 * Format object to media type.
 *
 * @param {object} obj
 * @return {string}
 * @public
 */

function format (obj) {
  if (!obj || typeof obj !== 'object') {
    throw new TypeError('argument obj is required')
  }

  var parameters = obj.parameters
  var type = obj.type

  if (!type || !TYPE_REGEXP.test(type)) {
    throw new TypeError('invalid type')
  }

  var string = type

  // append parameters
  if (parameters && typeof parameters === 'object') {
    var param
    var params = Object.keys(parameters).sort()

    for (var i = 0; i < params.length; i++) {
      param = params[i]

      if (!TOKEN_REGEXP.test(param)) {
        throw new TypeError('invalid parameter name')
      }

      string += '; ' + param + '=' + qstring(parameters[param])
    }
  }

  return string
}

/**
 * Parse media type to object.
 *
 * @param {string|object} string
 * @return {Object}
 * @public
 */

function parse (string) {
  if (!string) {
    throw new TypeError('argument string is required')
  }

  // support req/res-like objects as argument
  var header = typeof string === 'object'
    ? getcontenttype(string)
    : string

  if (typeof header !== 'string') {
    throw new TypeError('argument string is required to be a string')
  }

  var index = header.indexOf(';')
  var type = index !== -1
    ? header.substr(0, index).trim()
    : header.trim()

  if (!TYPE_REGEXP.test(type)) {
    throw new TypeError('invalid media type')
  }

  var obj = new ContentType(type.toLowerCase())

  // parse parameters
  if (index !== -1) {
    var key
    var match
    var value

    PARAM_REGEXP.lastIndex = index

    while ((match = PARAM_REGEXP.exec(header))) {
      if (match.index !== index) {
        throw new TypeError('invalid parameter format')
      }

      index += match[0].length
      key = match[1].toLowerCase()
      value = match[2]

      if (value[0] === '"') {
        // remove quotes and escapes
        value = value
          .substr(1, value.length - 2)
          .replace(QESC_REGEXP, '$1')
      }

      obj.parameters[key] = value
    }

    if (index !== header.length) {
      throw new TypeError('invalid parameter format')
    }
  }

  return obj
}

/**
 * Get content-type from req/res objects.
 *
 * @param {object}
 * @return {Object}
 * @private
 */

function getcontenttype (obj) {
  var header

  if (typeof obj.getHeader === 'function') {
    // res-like
    header = obj.getHeader('content-type')
  } else if (typeof obj.headers === 'object') {
    // req-like
    header = obj.headers && obj.headers['content-type']
  }

  if (typeof header !== 'string') {
    throw new TypeError('content-type header is missing from object')
  }

  return header
}

/**
 * Quote a string if necessary.
 *
 * @param {string} val
 * @return {string}
 * @private
 */

function qstring (val) {
  var str = String(val)

  // no need to quote tokens
  if (TOKEN_REGEXP.test(str)) {
    return str
  }

  if (str.length > 0 && !TEXT_REGEXP.test(str)) {
    throw new TypeError('invalid parameter value')
  }

  return '"' + str.replace(QUOTE_REGEXP, '\\$1') + '"'
}

/**
 * Class to represent a content type.
 * @private
 */
function ContentType (type) {
  this.parameters = Object.create(null)
  this.type = type
}

},{}],5:[function(require,module,exports){
/*! @license DOMPurify 2.3.5 | (c) Cure53 and other contributors | Released under the Apache license 2.0 and Mozilla Public License 2.0 | github.com/cure53/DOMPurify/blob/2.3.5/LICENSE */

(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
  typeof define === 'function' && define.amd ? define(factory) :
  (global = global || self, global.DOMPurify = factory());
}(this, function () { 'use strict';

  function _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }

  var hasOwnProperty = Object.hasOwnProperty,
      setPrototypeOf = Object.setPrototypeOf,
      isFrozen = Object.isFrozen,
      getPrototypeOf = Object.getPrototypeOf,
      getOwnPropertyDescriptor = Object.getOwnPropertyDescriptor;
  var freeze = Object.freeze,
      seal = Object.seal,
      create = Object.create; // eslint-disable-line import/no-mutable-exports

  var _ref = typeof Reflect !== 'undefined' && Reflect,
      apply = _ref.apply,
      construct = _ref.construct;

  if (!apply) {
    apply = function apply(fun, thisValue, args) {
      return fun.apply(thisValue, args);
    };
  }

  if (!freeze) {
    freeze = function freeze(x) {
      return x;
    };
  }

  if (!seal) {
    seal = function seal(x) {
      return x;
    };
  }

  if (!construct) {
    construct = function construct(Func, args) {
      return new (Function.prototype.bind.apply(Func, [null].concat(_toConsumableArray(args))))();
    };
  }

  var arrayForEach = unapply(Array.prototype.forEach);
  var arrayPop = unapply(Array.prototype.pop);
  var arrayPush = unapply(Array.prototype.push);

  var stringToLowerCase = unapply(String.prototype.toLowerCase);
  var stringMatch = unapply(String.prototype.match);
  var stringReplace = unapply(String.prototype.replace);
  var stringIndexOf = unapply(String.prototype.indexOf);
  var stringTrim = unapply(String.prototype.trim);

  var regExpTest = unapply(RegExp.prototype.test);

  var typeErrorCreate = unconstruct(TypeError);

  function unapply(func) {
    return function (thisArg) {
      for (var _len = arguments.length, args = Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
        args[_key - 1] = arguments[_key];
      }

      return apply(func, thisArg, args);
    };
  }

  function unconstruct(func) {
    return function () {
      for (var _len2 = arguments.length, args = Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
        args[_key2] = arguments[_key2];
      }

      return construct(func, args);
    };
  }

  /* Add properties to a lookup table */
  function addToSet(set, array) {
    if (setPrototypeOf) {
      // Make 'in' and truthy checks like Boolean(set.constructor)
      // independent of any properties defined on Object.prototype.
      // Prevent prototype setters from intercepting set as a this value.
      setPrototypeOf(set, null);
    }

    var l = array.length;
    while (l--) {
      var element = array[l];
      if (typeof element === 'string') {
        var lcElement = stringToLowerCase(element);
        if (lcElement !== element) {
          // Config presets (e.g. tags.js, attrs.js) are immutable.
          if (!isFrozen(array)) {
            array[l] = lcElement;
          }

          element = lcElement;
        }
      }

      set[element] = true;
    }

    return set;
  }

  /* Shallow clone an object */
  function clone(object) {
    var newObject = create(null);

    var property = void 0;
    for (property in object) {
      if (apply(hasOwnProperty, object, [property])) {
        newObject[property] = object[property];
      }
    }

    return newObject;
  }

  /* IE10 doesn't support __lookupGetter__ so lets'
   * simulate it. It also automatically checks
   * if the prop is function or getter and behaves
   * accordingly. */
  function lookupGetter(object, prop) {
    while (object !== null) {
      var desc = getOwnPropertyDescriptor(object, prop);
      if (desc) {
        if (desc.get) {
          return unapply(desc.get);
        }

        if (typeof desc.value === 'function') {
          return unapply(desc.value);
        }
      }

      object = getPrototypeOf(object);
    }

    function fallbackValue(element) {
      console.warn('fallback value for', element);
      return null;
    }

    return fallbackValue;
  }

  var html = freeze(['a', 'abbr', 'acronym', 'address', 'area', 'article', 'aside', 'audio', 'b', 'bdi', 'bdo', 'big', 'blink', 'blockquote', 'body', 'br', 'button', 'canvas', 'caption', 'center', 'cite', 'code', 'col', 'colgroup', 'content', 'data', 'datalist', 'dd', 'decorator', 'del', 'details', 'dfn', 'dialog', 'dir', 'div', 'dl', 'dt', 'element', 'em', 'fieldset', 'figcaption', 'figure', 'font', 'footer', 'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'head', 'header', 'hgroup', 'hr', 'html', 'i', 'img', 'input', 'ins', 'kbd', 'label', 'legend', 'li', 'main', 'map', 'mark', 'marquee', 'menu', 'menuitem', 'meter', 'nav', 'nobr', 'ol', 'optgroup', 'option', 'output', 'p', 'picture', 'pre', 'progress', 'q', 'rp', 'rt', 'ruby', 's', 'samp', 'section', 'select', 'shadow', 'small', 'source', 'spacer', 'span', 'strike', 'strong', 'style', 'sub', 'summary', 'sup', 'table', 'tbody', 'td', 'template', 'textarea', 'tfoot', 'th', 'thead', 'time', 'tr', 'track', 'tt', 'u', 'ul', 'var', 'video', 'wbr']);

  // SVG
  var svg = freeze(['svg', 'a', 'altglyph', 'altglyphdef', 'altglyphitem', 'animatecolor', 'animatemotion', 'animatetransform', 'circle', 'clippath', 'defs', 'desc', 'ellipse', 'filter', 'font', 'g', 'glyph', 'glyphref', 'hkern', 'image', 'line', 'lineargradient', 'marker', 'mask', 'metadata', 'mpath', 'path', 'pattern', 'polygon', 'polyline', 'radialgradient', 'rect', 'stop', 'style', 'switch', 'symbol', 'text', 'textpath', 'title', 'tref', 'tspan', 'view', 'vkern']);

  var svgFilters = freeze(['feBlend', 'feColorMatrix', 'feComponentTransfer', 'feComposite', 'feConvolveMatrix', 'feDiffuseLighting', 'feDisplacementMap', 'feDistantLight', 'feFlood', 'feFuncA', 'feFuncB', 'feFuncG', 'feFuncR', 'feGaussianBlur', 'feImage', 'feMerge', 'feMergeNode', 'feMorphology', 'feOffset', 'fePointLight', 'feSpecularLighting', 'feSpotLight', 'feTile', 'feTurbulence']);

  // List of SVG elements that are disallowed by default.
  // We still need to know them so that we can do namespace
  // checks properly in case one wants to add them to
  // allow-list.
  var svgDisallowed = freeze(['animate', 'color-profile', 'cursor', 'discard', 'fedropshadow', 'font-face', 'font-face-format', 'font-face-name', 'font-face-src', 'font-face-uri', 'foreignobject', 'hatch', 'hatchpath', 'mesh', 'meshgradient', 'meshpatch', 'meshrow', 'missing-glyph', 'script', 'set', 'solidcolor', 'unknown', 'use']);

  var mathMl = freeze(['math', 'menclose', 'merror', 'mfenced', 'mfrac', 'mglyph', 'mi', 'mlabeledtr', 'mmultiscripts', 'mn', 'mo', 'mover', 'mpadded', 'mphantom', 'mroot', 'mrow', 'ms', 'mspace', 'msqrt', 'mstyle', 'msub', 'msup', 'msubsup', 'mtable', 'mtd', 'mtext', 'mtr', 'munder', 'munderover']);

  // Similarly to SVG, we want to know all MathML elements,
  // even those that we disallow by default.
  var mathMlDisallowed = freeze(['maction', 'maligngroup', 'malignmark', 'mlongdiv', 'mscarries', 'mscarry', 'msgroup', 'mstack', 'msline', 'msrow', 'semantics', 'annotation', 'annotation-xml', 'mprescripts', 'none']);

  var text = freeze(['#text']);

  var html$1 = freeze(['accept', 'action', 'align', 'alt', 'autocapitalize', 'autocomplete', 'autopictureinpicture', 'autoplay', 'background', 'bgcolor', 'border', 'capture', 'cellpadding', 'cellspacing', 'checked', 'cite', 'class', 'clear', 'color', 'cols', 'colspan', 'controls', 'controlslist', 'coords', 'crossorigin', 'datetime', 'decoding', 'default', 'dir', 'disabled', 'disablepictureinpicture', 'disableremoteplayback', 'download', 'draggable', 'enctype', 'enterkeyhint', 'face', 'for', 'headers', 'height', 'hidden', 'high', 'href', 'hreflang', 'id', 'inputmode', 'integrity', 'ismap', 'kind', 'label', 'lang', 'list', 'loading', 'loop', 'low', 'max', 'maxlength', 'media', 'method', 'min', 'minlength', 'multiple', 'muted', 'name', 'nonce', 'noshade', 'novalidate', 'nowrap', 'open', 'optimum', 'pattern', 'placeholder', 'playsinline', 'poster', 'preload', 'pubdate', 'radiogroup', 'readonly', 'rel', 'required', 'rev', 'reversed', 'role', 'rows', 'rowspan', 'spellcheck', 'scope', 'selected', 'shape', 'size', 'sizes', 'span', 'srclang', 'start', 'src', 'srcset', 'step', 'style', 'summary', 'tabindex', 'title', 'translate', 'type', 'usemap', 'valign', 'value', 'width', 'xmlns', 'slot']);

  var svg$1 = freeze(['accent-height', 'accumulate', 'additive', 'alignment-baseline', 'ascent', 'attributename', 'attributetype', 'azimuth', 'basefrequency', 'baseline-shift', 'begin', 'bias', 'by', 'class', 'clip', 'clippathunits', 'clip-path', 'clip-rule', 'color', 'color-interpolation', 'color-interpolation-filters', 'color-profile', 'color-rendering', 'cx', 'cy', 'd', 'dx', 'dy', 'diffuseconstant', 'direction', 'display', 'divisor', 'dur', 'edgemode', 'elevation', 'end', 'fill', 'fill-opacity', 'fill-rule', 'filter', 'filterunits', 'flood-color', 'flood-opacity', 'font-family', 'font-size', 'font-size-adjust', 'font-stretch', 'font-style', 'font-variant', 'font-weight', 'fx', 'fy', 'g1', 'g2', 'glyph-name', 'glyphref', 'gradientunits', 'gradienttransform', 'height', 'href', 'id', 'image-rendering', 'in', 'in2', 'k', 'k1', 'k2', 'k3', 'k4', 'kerning', 'keypoints', 'keysplines', 'keytimes', 'lang', 'lengthadjust', 'letter-spacing', 'kernelmatrix', 'kernelunitlength', 'lighting-color', 'local', 'marker-end', 'marker-mid', 'marker-start', 'markerheight', 'markerunits', 'markerwidth', 'maskcontentunits', 'maskunits', 'max', 'mask', 'media', 'method', 'mode', 'min', 'name', 'numoctaves', 'offset', 'operator', 'opacity', 'order', 'orient', 'orientation', 'origin', 'overflow', 'paint-order', 'path', 'pathlength', 'patterncontentunits', 'patterntransform', 'patternunits', 'points', 'preservealpha', 'preserveaspectratio', 'primitiveunits', 'r', 'rx', 'ry', 'radius', 'refx', 'refy', 'repeatcount', 'repeatdur', 'restart', 'result', 'rotate', 'scale', 'seed', 'shape-rendering', 'specularconstant', 'specularexponent', 'spreadmethod', 'startoffset', 'stddeviation', 'stitchtiles', 'stop-color', 'stop-opacity', 'stroke-dasharray', 'stroke-dashoffset', 'stroke-linecap', 'stroke-linejoin', 'stroke-miterlimit', 'stroke-opacity', 'stroke', 'stroke-width', 'style', 'surfacescale', 'systemlanguage', 'tabindex', 'targetx', 'targety', 'transform', 'transform-origin', 'text-anchor', 'text-decoration', 'text-rendering', 'textlength', 'type', 'u1', 'u2', 'unicode', 'values', 'viewbox', 'visibility', 'version', 'vert-adv-y', 'vert-origin-x', 'vert-origin-y', 'width', 'word-spacing', 'wrap', 'writing-mode', 'xchannelselector', 'ychannelselector', 'x', 'x1', 'x2', 'xmlns', 'y', 'y1', 'y2', 'z', 'zoomandpan']);

  var mathMl$1 = freeze(['accent', 'accentunder', 'align', 'bevelled', 'close', 'columnsalign', 'columnlines', 'columnspan', 'denomalign', 'depth', 'dir', 'display', 'displaystyle', 'encoding', 'fence', 'frame', 'height', 'href', 'id', 'largeop', 'length', 'linethickness', 'lspace', 'lquote', 'mathbackground', 'mathcolor', 'mathsize', 'mathvariant', 'maxsize', 'minsize', 'movablelimits', 'notation', 'numalign', 'open', 'rowalign', 'rowlines', 'rowspacing', 'rowspan', 'rspace', 'rquote', 'scriptlevel', 'scriptminsize', 'scriptsizemultiplier', 'selection', 'separator', 'separators', 'stretchy', 'subscriptshift', 'supscriptshift', 'symmetric', 'voffset', 'width', 'xmlns']);

  var xml = freeze(['xlink:href', 'xml:id', 'xlink:title', 'xml:space', 'xmlns:xlink']);

  // eslint-disable-next-line unicorn/better-regex
  var MUSTACHE_EXPR = seal(/\{\{[\s\S]*|[\s\S]*\}\}/gm); // Specify template detection regex for SAFE_FOR_TEMPLATES mode
  var ERB_EXPR = seal(/<%[\s\S]*|[\s\S]*%>/gm);
  var DATA_ATTR = seal(/^data-[\-\w.\u00B7-\uFFFF]/); // eslint-disable-line no-useless-escape
  var ARIA_ATTR = seal(/^aria-[\-\w]+$/); // eslint-disable-line no-useless-escape
  var IS_ALLOWED_URI = seal(/^(?:(?:(?:f|ht)tps?|mailto|tel|callto|cid|xmpp):|[^a-z]|[a-z+.\-]+(?:[^a-z+.\-:]|$))/i // eslint-disable-line no-useless-escape
  );
  var IS_SCRIPT_OR_DATA = seal(/^(?:\w+script|data):/i);
  var ATTR_WHITESPACE = seal(/[\u0000-\u0020\u00A0\u1680\u180E\u2000-\u2029\u205F\u3000]/g // eslint-disable-line no-control-regex
  );

  var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

  function _toConsumableArray$1(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }

  var getGlobal = function getGlobal() {
    return typeof window === 'undefined' ? null : window;
  };

  /**
   * Creates a no-op policy for internal use only.
   * Don't export this function outside this module!
   * @param {?TrustedTypePolicyFactory} trustedTypes The policy factory.
   * @param {Document} document The document object (to determine policy name suffix)
   * @return {?TrustedTypePolicy} The policy created (or null, if Trusted Types
   * are not supported).
   */
  var _createTrustedTypesPolicy = function _createTrustedTypesPolicy(trustedTypes, document) {
    if ((typeof trustedTypes === 'undefined' ? 'undefined' : _typeof(trustedTypes)) !== 'object' || typeof trustedTypes.createPolicy !== 'function') {
      return null;
    }

    // Allow the callers to control the unique policy name
    // by adding a data-tt-policy-suffix to the script element with the DOMPurify.
    // Policy creation with duplicate names throws in Trusted Types.
    var suffix = null;
    var ATTR_NAME = 'data-tt-policy-suffix';
    if (document.currentScript && document.currentScript.hasAttribute(ATTR_NAME)) {
      suffix = document.currentScript.getAttribute(ATTR_NAME);
    }

    var policyName = 'dompurify' + (suffix ? '#' + suffix : '');

    try {
      return trustedTypes.createPolicy(policyName, {
        createHTML: function createHTML(html$$1) {
          return html$$1;
        }
      });
    } catch (_) {
      // Policy creation failed (most likely another DOMPurify script has
      // already run). Skip creating the policy, as this will only cause errors
      // if TT are enforced.
      console.warn('TrustedTypes policy ' + policyName + ' could not be created.');
      return null;
    }
  };

  function createDOMPurify() {
    var window = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : getGlobal();

    var DOMPurify = function DOMPurify(root) {
      return createDOMPurify(root);
    };

    /**
     * Version label, exposed for easier checks
     * if DOMPurify is up to date or not
     */
    DOMPurify.version = '2.3.5';

    /**
     * Array of elements that DOMPurify removed during sanitation.
     * Empty if nothing was removed.
     */
    DOMPurify.removed = [];

    if (!window || !window.document || window.document.nodeType !== 9) {
      // Not running in a browser, provide a factory function
      // so that you can pass your own Window
      DOMPurify.isSupported = false;

      return DOMPurify;
    }

    var originalDocument = window.document;

    var document = window.document;
    var DocumentFragment = window.DocumentFragment,
        HTMLTemplateElement = window.HTMLTemplateElement,
        Node = window.Node,
        Element = window.Element,
        NodeFilter = window.NodeFilter,
        _window$NamedNodeMap = window.NamedNodeMap,
        NamedNodeMap = _window$NamedNodeMap === undefined ? window.NamedNodeMap || window.MozNamedAttrMap : _window$NamedNodeMap,
        HTMLFormElement = window.HTMLFormElement,
        DOMParser = window.DOMParser,
        trustedTypes = window.trustedTypes;


    var ElementPrototype = Element.prototype;

    var cloneNode = lookupGetter(ElementPrototype, 'cloneNode');
    var getNextSibling = lookupGetter(ElementPrototype, 'nextSibling');
    var getChildNodes = lookupGetter(ElementPrototype, 'childNodes');
    var getParentNode = lookupGetter(ElementPrototype, 'parentNode');

    // As per issue #47, the web-components registry is inherited by a
    // new document created via createHTMLDocument. As per the spec
    // (http://w3c.github.io/webcomponents/spec/custom/#creating-and-passing-registries)
    // a new empty registry is used when creating a template contents owner
    // document, so we use that as our parent document to ensure nothing
    // is inherited.
    if (typeof HTMLTemplateElement === 'function') {
      var template = document.createElement('template');
      if (template.content && template.content.ownerDocument) {
        document = template.content.ownerDocument;
      }
    }

    var trustedTypesPolicy = _createTrustedTypesPolicy(trustedTypes, originalDocument);
    var emptyHTML = trustedTypesPolicy ? trustedTypesPolicy.createHTML('') : '';

    var _document = document,
        implementation = _document.implementation,
        createNodeIterator = _document.createNodeIterator,
        createDocumentFragment = _document.createDocumentFragment,
        getElementsByTagName = _document.getElementsByTagName;
    var importNode = originalDocument.importNode;


    var documentMode = {};
    try {
      documentMode = clone(document).documentMode ? document.documentMode : {};
    } catch (_) {}

    var hooks = {};

    /**
     * Expose whether this browser supports running the full DOMPurify.
     */
    DOMPurify.isSupported = typeof getParentNode === 'function' && implementation && typeof implementation.createHTMLDocument !== 'undefined' && documentMode !== 9;

    var MUSTACHE_EXPR$$1 = MUSTACHE_EXPR,
        ERB_EXPR$$1 = ERB_EXPR,
        DATA_ATTR$$1 = DATA_ATTR,
        ARIA_ATTR$$1 = ARIA_ATTR,
        IS_SCRIPT_OR_DATA$$1 = IS_SCRIPT_OR_DATA,
        ATTR_WHITESPACE$$1 = ATTR_WHITESPACE;
    var IS_ALLOWED_URI$$1 = IS_ALLOWED_URI;

    /**
     * We consider the elements and attributes below to be safe. Ideally
     * don't add any new ones but feel free to remove unwanted ones.
     */

    /* allowed element names */

    var ALLOWED_TAGS = null;
    var DEFAULT_ALLOWED_TAGS = addToSet({}, [].concat(_toConsumableArray$1(html), _toConsumableArray$1(svg), _toConsumableArray$1(svgFilters), _toConsumableArray$1(mathMl), _toConsumableArray$1(text)));

    /* Allowed attribute names */
    var ALLOWED_ATTR = null;
    var DEFAULT_ALLOWED_ATTR = addToSet({}, [].concat(_toConsumableArray$1(html$1), _toConsumableArray$1(svg$1), _toConsumableArray$1(mathMl$1), _toConsumableArray$1(xml)));

    /*
     * Configure how DOMPUrify should handle custom elements and their attributes as well as customized built-in elements.
     * @property {RegExp|Function|null} tagNameCheck one of [null, regexPattern, predicate]. Default: `null` (disallow any custom elements)
     * @property {RegExp|Function|null} attributeNameCheck one of [null, regexPattern, predicate]. Default: `null` (disallow any attributes not on the allow list)
     * @property {boolean} allowCustomizedBuiltInElements allow custom elements derived from built-ins if they pass CUSTOM_ELEMENT_HANDLING.tagNameCheck. Default: `false`.
     */
    var CUSTOM_ELEMENT_HANDLING = Object.seal(Object.create(null, {
      tagNameCheck: {
        writable: true,
        configurable: false,
        enumerable: true,
        value: null
      },
      attributeNameCheck: {
        writable: true,
        configurable: false,
        enumerable: true,
        value: null
      },
      allowCustomizedBuiltInElements: {
        writable: true,
        configurable: false,
        enumerable: true,
        value: false
      }
    }));

    /* Explicitly forbidden tags (overrides ALLOWED_TAGS/ADD_TAGS) */
    var FORBID_TAGS = null;

    /* Explicitly forbidden attributes (overrides ALLOWED_ATTR/ADD_ATTR) */
    var FORBID_ATTR = null;

    /* Decide if ARIA attributes are okay */
    var ALLOW_ARIA_ATTR = true;

    /* Decide if custom data attributes are okay */
    var ALLOW_DATA_ATTR = true;

    /* Decide if unknown protocols are okay */
    var ALLOW_UNKNOWN_PROTOCOLS = false;

    /* Output should be safe for common template engines.
     * This means, DOMPurify removes data attributes, mustaches and ERB
     */
    var SAFE_FOR_TEMPLATES = false;

    /* Decide if document with <html>... should be returned */
    var WHOLE_DOCUMENT = false;

    /* Track whether config is already set on this instance of DOMPurify. */
    var SET_CONFIG = false;

    /* Decide if all elements (e.g. style, script) must be children of
     * document.body. By default, browsers might move them to document.head */
    var FORCE_BODY = false;

    /* Decide if a DOM `HTMLBodyElement` should be returned, instead of a html
     * string (or a TrustedHTML object if Trusted Types are supported).
     * If `WHOLE_DOCUMENT` is enabled a `HTMLHtmlElement` will be returned instead
     */
    var RETURN_DOM = false;

    /* Decide if a DOM `DocumentFragment` should be returned, instead of a html
     * string  (or a TrustedHTML object if Trusted Types are supported) */
    var RETURN_DOM_FRAGMENT = false;

    /* Try to return a Trusted Type object instead of a string, return a string in
     * case Trusted Types are not supported  */
    var RETURN_TRUSTED_TYPE = false;

    /* Output should be free from DOM clobbering attacks? */
    var SANITIZE_DOM = true;

    /* Keep element content when removing element? */
    var KEEP_CONTENT = true;

    /* If a `Node` is passed to sanitize(), then performs sanitization in-place instead
     * of importing it into a new Document and returning a sanitized copy */
    var IN_PLACE = false;

    /* Allow usage of profiles like html, svg and mathMl */
    var USE_PROFILES = {};

    /* Tags to ignore content of when KEEP_CONTENT is true */
    var FORBID_CONTENTS = null;
    var DEFAULT_FORBID_CONTENTS = addToSet({}, ['annotation-xml', 'audio', 'colgroup', 'desc', 'foreignobject', 'head', 'iframe', 'math', 'mi', 'mn', 'mo', 'ms', 'mtext', 'noembed', 'noframes', 'noscript', 'plaintext', 'script', 'style', 'svg', 'template', 'thead', 'title', 'video', 'xmp']);

    /* Tags that are safe for data: URIs */
    var DATA_URI_TAGS = null;
    var DEFAULT_DATA_URI_TAGS = addToSet({}, ['audio', 'video', 'img', 'source', 'image', 'track']);

    /* Attributes safe for values like "javascript:" */
    var URI_SAFE_ATTRIBUTES = null;
    var DEFAULT_URI_SAFE_ATTRIBUTES = addToSet({}, ['alt', 'class', 'for', 'id', 'label', 'name', 'pattern', 'placeholder', 'role', 'summary', 'title', 'value', 'style', 'xmlns']);

    var MATHML_NAMESPACE = 'http://www.w3.org/1998/Math/MathML';
    var SVG_NAMESPACE = 'http://www.w3.org/2000/svg';
    var HTML_NAMESPACE = 'http://www.w3.org/1999/xhtml';
    /* Document namespace */
    var NAMESPACE = HTML_NAMESPACE;
    var IS_EMPTY_INPUT = false;

    /* Parsing of strict XHTML documents */
    var PARSER_MEDIA_TYPE = void 0;
    var SUPPORTED_PARSER_MEDIA_TYPES = ['application/xhtml+xml', 'text/html'];
    var DEFAULT_PARSER_MEDIA_TYPE = 'text/html';
    var transformCaseFunc = void 0;

    /* Keep a reference to config to pass to hooks */
    var CONFIG = null;

    /* Ideally, do not touch anything below this line */
    /* ______________________________________________ */

    var formElement = document.createElement('form');

    var isRegexOrFunction = function isRegexOrFunction(testValue) {
      return testValue instanceof RegExp || testValue instanceof Function;
    };

    /**
     * _parseConfig
     *
     * @param  {Object} cfg optional config literal
     */
    // eslint-disable-next-line complexity
    var _parseConfig = function _parseConfig(cfg) {
      if (CONFIG && CONFIG === cfg) {
        return;
      }

      /* Shield configuration object from tampering */
      if (!cfg || (typeof cfg === 'undefined' ? 'undefined' : _typeof(cfg)) !== 'object') {
        cfg = {};
      }

      /* Shield configuration object from prototype pollution */
      cfg = clone(cfg);

      /* Set configuration parameters */
      ALLOWED_TAGS = 'ALLOWED_TAGS' in cfg ? addToSet({}, cfg.ALLOWED_TAGS) : DEFAULT_ALLOWED_TAGS;
      ALLOWED_ATTR = 'ALLOWED_ATTR' in cfg ? addToSet({}, cfg.ALLOWED_ATTR) : DEFAULT_ALLOWED_ATTR;
      URI_SAFE_ATTRIBUTES = 'ADD_URI_SAFE_ATTR' in cfg ? addToSet(clone(DEFAULT_URI_SAFE_ATTRIBUTES), cfg.ADD_URI_SAFE_ATTR) : DEFAULT_URI_SAFE_ATTRIBUTES;
      DATA_URI_TAGS = 'ADD_DATA_URI_TAGS' in cfg ? addToSet(clone(DEFAULT_DATA_URI_TAGS), cfg.ADD_DATA_URI_TAGS) : DEFAULT_DATA_URI_TAGS;
      FORBID_CONTENTS = 'FORBID_CONTENTS' in cfg ? addToSet({}, cfg.FORBID_CONTENTS) : DEFAULT_FORBID_CONTENTS;
      FORBID_TAGS = 'FORBID_TAGS' in cfg ? addToSet({}, cfg.FORBID_TAGS) : {};
      FORBID_ATTR = 'FORBID_ATTR' in cfg ? addToSet({}, cfg.FORBID_ATTR) : {};
      USE_PROFILES = 'USE_PROFILES' in cfg ? cfg.USE_PROFILES : false;
      ALLOW_ARIA_ATTR = cfg.ALLOW_ARIA_ATTR !== false; // Default true
      ALLOW_DATA_ATTR = cfg.ALLOW_DATA_ATTR !== false; // Default true
      ALLOW_UNKNOWN_PROTOCOLS = cfg.ALLOW_UNKNOWN_PROTOCOLS || false; // Default false
      SAFE_FOR_TEMPLATES = cfg.SAFE_FOR_TEMPLATES || false; // Default false
      WHOLE_DOCUMENT = cfg.WHOLE_DOCUMENT || false; // Default false
      RETURN_DOM = cfg.RETURN_DOM || false; // Default false
      RETURN_DOM_FRAGMENT = cfg.RETURN_DOM_FRAGMENT || false; // Default false
      RETURN_TRUSTED_TYPE = cfg.RETURN_TRUSTED_TYPE || false; // Default false
      FORCE_BODY = cfg.FORCE_BODY || false; // Default false
      SANITIZE_DOM = cfg.SANITIZE_DOM !== false; // Default true
      KEEP_CONTENT = cfg.KEEP_CONTENT !== false; // Default true
      IN_PLACE = cfg.IN_PLACE || false; // Default false
      IS_ALLOWED_URI$$1 = cfg.ALLOWED_URI_REGEXP || IS_ALLOWED_URI$$1;
      NAMESPACE = cfg.NAMESPACE || HTML_NAMESPACE;
      if (cfg.CUSTOM_ELEMENT_HANDLING && isRegexOrFunction(cfg.CUSTOM_ELEMENT_HANDLING.tagNameCheck)) {
        CUSTOM_ELEMENT_HANDLING.tagNameCheck = cfg.CUSTOM_ELEMENT_HANDLING.tagNameCheck;
      }

      if (cfg.CUSTOM_ELEMENT_HANDLING && isRegexOrFunction(cfg.CUSTOM_ELEMENT_HANDLING.attributeNameCheck)) {
        CUSTOM_ELEMENT_HANDLING.attributeNameCheck = cfg.CUSTOM_ELEMENT_HANDLING.attributeNameCheck;
      }

      if (cfg.CUSTOM_ELEMENT_HANDLING && typeof cfg.CUSTOM_ELEMENT_HANDLING.allowCustomizedBuiltInElements === 'boolean') {
        CUSTOM_ELEMENT_HANDLING.allowCustomizedBuiltInElements = cfg.CUSTOM_ELEMENT_HANDLING.allowCustomizedBuiltInElements;
      }

      PARSER_MEDIA_TYPE =
      // eslint-disable-next-line unicorn/prefer-includes
      SUPPORTED_PARSER_MEDIA_TYPES.indexOf(cfg.PARSER_MEDIA_TYPE) === -1 ? PARSER_MEDIA_TYPE = DEFAULT_PARSER_MEDIA_TYPE : PARSER_MEDIA_TYPE = cfg.PARSER_MEDIA_TYPE;

      // HTML tags and attributes are not case-sensitive, converting to lowercase. Keeping XHTML as is.
      transformCaseFunc = PARSER_MEDIA_TYPE === 'application/xhtml+xml' ? function (x) {
        return x;
      } : stringToLowerCase;

      if (SAFE_FOR_TEMPLATES) {
        ALLOW_DATA_ATTR = false;
      }

      if (RETURN_DOM_FRAGMENT) {
        RETURN_DOM = true;
      }

      /* Parse profile info */
      if (USE_PROFILES) {
        ALLOWED_TAGS = addToSet({}, [].concat(_toConsumableArray$1(text)));
        ALLOWED_ATTR = [];
        if (USE_PROFILES.html === true) {
          addToSet(ALLOWED_TAGS, html);
          addToSet(ALLOWED_ATTR, html$1);
        }

        if (USE_PROFILES.svg === true) {
          addToSet(ALLOWED_TAGS, svg);
          addToSet(ALLOWED_ATTR, svg$1);
          addToSet(ALLOWED_ATTR, xml);
        }

        if (USE_PROFILES.svgFilters === true) {
          addToSet(ALLOWED_TAGS, svgFilters);
          addToSet(ALLOWED_ATTR, svg$1);
          addToSet(ALLOWED_ATTR, xml);
        }

        if (USE_PROFILES.mathMl === true) {
          addToSet(ALLOWED_TAGS, mathMl);
          addToSet(ALLOWED_ATTR, mathMl$1);
          addToSet(ALLOWED_ATTR, xml);
        }
      }

      /* Merge configuration parameters */
      if (cfg.ADD_TAGS) {
        if (ALLOWED_TAGS === DEFAULT_ALLOWED_TAGS) {
          ALLOWED_TAGS = clone(ALLOWED_TAGS);
        }

        addToSet(ALLOWED_TAGS, cfg.ADD_TAGS);
      }

      if (cfg.ADD_ATTR) {
        if (ALLOWED_ATTR === DEFAULT_ALLOWED_ATTR) {
          ALLOWED_ATTR = clone(ALLOWED_ATTR);
        }

        addToSet(ALLOWED_ATTR, cfg.ADD_ATTR);
      }

      if (cfg.ADD_URI_SAFE_ATTR) {
        addToSet(URI_SAFE_ATTRIBUTES, cfg.ADD_URI_SAFE_ATTR);
      }

      if (cfg.FORBID_CONTENTS) {
        if (FORBID_CONTENTS === DEFAULT_FORBID_CONTENTS) {
          FORBID_CONTENTS = clone(FORBID_CONTENTS);
        }

        addToSet(FORBID_CONTENTS, cfg.FORBID_CONTENTS);
      }

      /* Add #text in case KEEP_CONTENT is set to true */
      if (KEEP_CONTENT) {
        ALLOWED_TAGS['#text'] = true;
      }

      /* Add html, head and body to ALLOWED_TAGS in case WHOLE_DOCUMENT is true */
      if (WHOLE_DOCUMENT) {
        addToSet(ALLOWED_TAGS, ['html', 'head', 'body']);
      }

      /* Add tbody to ALLOWED_TAGS in case tables are permitted, see #286, #365 */
      if (ALLOWED_TAGS.table) {
        addToSet(ALLOWED_TAGS, ['tbody']);
        delete FORBID_TAGS.tbody;
      }

      // Prevent further manipulation of configuration.
      // Not available in IE8, Safari 5, etc.
      if (freeze) {
        freeze(cfg);
      }

      CONFIG = cfg;
    };

    var MATHML_TEXT_INTEGRATION_POINTS = addToSet({}, ['mi', 'mo', 'mn', 'ms', 'mtext']);

    var HTML_INTEGRATION_POINTS = addToSet({}, ['foreignobject', 'desc', 'title', 'annotation-xml']);

    /* Keep track of all possible SVG and MathML tags
     * so that we can perform the namespace checks
     * correctly. */
    var ALL_SVG_TAGS = addToSet({}, svg);
    addToSet(ALL_SVG_TAGS, svgFilters);
    addToSet(ALL_SVG_TAGS, svgDisallowed);

    var ALL_MATHML_TAGS = addToSet({}, mathMl);
    addToSet(ALL_MATHML_TAGS, mathMlDisallowed);

    /**
     *
     *
     * @param  {Element} element a DOM element whose namespace is being checked
     * @returns {boolean} Return false if the element has a
     *  namespace that a spec-compliant parser would never
     *  return. Return true otherwise.
     */
    var _checkValidNamespace = function _checkValidNamespace(element) {
      var parent = getParentNode(element);

      // In JSDOM, if we're inside shadow DOM, then parentNode
      // can be null. We just simulate parent in this case.
      if (!parent || !parent.tagName) {
        parent = {
          namespaceURI: HTML_NAMESPACE,
          tagName: 'template'
        };
      }

      var tagName = stringToLowerCase(element.tagName);
      var parentTagName = stringToLowerCase(parent.tagName);

      if (element.namespaceURI === SVG_NAMESPACE) {
        // The only way to switch from HTML namespace to SVG
        // is via <svg>. If it happens via any other tag, then
        // it should be killed.
        if (parent.namespaceURI === HTML_NAMESPACE) {
          return tagName === 'svg';
        }

        // The only way to switch from MathML to SVG is via
        // svg if parent is either <annotation-xml> or MathML
        // text integration points.
        if (parent.namespaceURI === MATHML_NAMESPACE) {
          return tagName === 'svg' && (parentTagName === 'annotation-xml' || MATHML_TEXT_INTEGRATION_POINTS[parentTagName]);
        }

        // We only allow elements that are defined in SVG
        // spec. All others are disallowed in SVG namespace.
        return Boolean(ALL_SVG_TAGS[tagName]);
      }

      if (element.namespaceURI === MATHML_NAMESPACE) {
        // The only way to switch from HTML namespace to MathML
        // is via <math>. If it happens via any other tag, then
        // it should be killed.
        if (parent.namespaceURI === HTML_NAMESPACE) {
          return tagName === 'math';
        }

        // The only way to switch from SVG to MathML is via
        // <math> and HTML integration points
        if (parent.namespaceURI === SVG_NAMESPACE) {
          return tagName === 'math' && HTML_INTEGRATION_POINTS[parentTagName];
        }

        // We only allow elements that are defined in MathML
        // spec. All others are disallowed in MathML namespace.
        return Boolean(ALL_MATHML_TAGS[tagName]);
      }

      if (element.namespaceURI === HTML_NAMESPACE) {
        // The only way to switch from SVG to HTML is via
        // HTML integration points, and from MathML to HTML
        // is via MathML text integration points
        if (parent.namespaceURI === SVG_NAMESPACE && !HTML_INTEGRATION_POINTS[parentTagName]) {
          return false;
        }

        if (parent.namespaceURI === MATHML_NAMESPACE && !MATHML_TEXT_INTEGRATION_POINTS[parentTagName]) {
          return false;
        }

        // Certain elements are allowed in both SVG and HTML
        // namespace. We need to specify them explicitly
        // so that they don't get erronously deleted from
        // HTML namespace.
        var commonSvgAndHTMLElements = addToSet({}, ['title', 'style', 'font', 'a', 'script']);

        // We disallow tags that are specific for MathML
        // or SVG and should never appear in HTML namespace
        return !ALL_MATHML_TAGS[tagName] && (commonSvgAndHTMLElements[tagName] || !ALL_SVG_TAGS[tagName]);
      }

      // The code should never reach this place (this means
      // that the element somehow got namespace that is not
      // HTML, SVG or MathML). Return false just in case.
      return false;
    };

    /**
     * _forceRemove
     *
     * @param  {Node} node a DOM node
     */
    var _forceRemove = function _forceRemove(node) {
      arrayPush(DOMPurify.removed, { element: node });
      try {
        // eslint-disable-next-line unicorn/prefer-dom-node-remove
        node.parentNode.removeChild(node);
      } catch (_) {
        try {
          node.outerHTML = emptyHTML;
        } catch (_) {
          node.remove();
        }
      }
    };

    /**
     * _removeAttribute
     *
     * @param  {String} name an Attribute name
     * @param  {Node} node a DOM node
     */
    var _removeAttribute = function _removeAttribute(name, node) {
      try {
        arrayPush(DOMPurify.removed, {
          attribute: node.getAttributeNode(name),
          from: node
        });
      } catch (_) {
        arrayPush(DOMPurify.removed, {
          attribute: null,
          from: node
        });
      }

      node.removeAttribute(name);

      // We void attribute values for unremovable "is"" attributes
      if (name === 'is' && !ALLOWED_ATTR[name]) {
        if (RETURN_DOM || RETURN_DOM_FRAGMENT) {
          try {
            _forceRemove(node);
          } catch (_) {}
        } else {
          try {
            node.setAttribute(name, '');
          } catch (_) {}
        }
      }
    };

    /**
     * _initDocument
     *
     * @param  {String} dirty a string of dirty markup
     * @return {Document} a DOM, filled with the dirty markup
     */
    var _initDocument = function _initDocument(dirty) {
      /* Create a HTML document */
      var doc = void 0;
      var leadingWhitespace = void 0;

      if (FORCE_BODY) {
        dirty = '<remove></remove>' + dirty;
      } else {
        /* If FORCE_BODY isn't used, leading whitespace needs to be preserved manually */
        var matches = stringMatch(dirty, /^[\r\n\t ]+/);
        leadingWhitespace = matches && matches[0];
      }

      if (PARSER_MEDIA_TYPE === 'application/xhtml+xml') {
        // Root of XHTML doc must contain xmlns declaration (see https://www.w3.org/TR/xhtml1/normative.html#strict)
        dirty = '<html xmlns="http://www.w3.org/1999/xhtml"><head></head><body>' + dirty + '</body></html>';
      }

      var dirtyPayload = trustedTypesPolicy ? trustedTypesPolicy.createHTML(dirty) : dirty;
      /*
       * Use the DOMParser API by default, fallback later if needs be
       * DOMParser not work for svg when has multiple root element.
       */
      if (NAMESPACE === HTML_NAMESPACE) {
        try {
          doc = new DOMParser().parseFromString(dirtyPayload, PARSER_MEDIA_TYPE);
        } catch (_) {}
      }

      /* Use createHTMLDocument in case DOMParser is not available */
      if (!doc || !doc.documentElement) {
        doc = implementation.createDocument(NAMESPACE, 'template', null);
        try {
          doc.documentElement.innerHTML = IS_EMPTY_INPUT ? '' : dirtyPayload;
        } catch (_) {
          // Syntax error if dirtyPayload is invalid xml
        }
      }

      var body = doc.body || doc.documentElement;

      if (dirty && leadingWhitespace) {
        body.insertBefore(document.createTextNode(leadingWhitespace), body.childNodes[0] || null);
      }

      /* Work on whole document or just its body */
      if (NAMESPACE === HTML_NAMESPACE) {
        return getElementsByTagName.call(doc, WHOLE_DOCUMENT ? 'html' : 'body')[0];
      }

      return WHOLE_DOCUMENT ? doc.documentElement : body;
    };

    /**
     * _createIterator
     *
     * @param  {Document} root document/fragment to create iterator for
     * @return {Iterator} iterator instance
     */
    var _createIterator = function _createIterator(root) {
      return createNodeIterator.call(root.ownerDocument || root, root, NodeFilter.SHOW_ELEMENT | NodeFilter.SHOW_COMMENT | NodeFilter.SHOW_TEXT, null, false);
    };

    /**
     * _isClobbered
     *
     * @param  {Node} elm element to check for clobbering attacks
     * @return {Boolean} true if clobbered, false if safe
     */
    var _isClobbered = function _isClobbered(elm) {
      return elm instanceof HTMLFormElement && (typeof elm.nodeName !== 'string' || typeof elm.textContent !== 'string' || typeof elm.removeChild !== 'function' || !(elm.attributes instanceof NamedNodeMap) || typeof elm.removeAttribute !== 'function' || typeof elm.setAttribute !== 'function' || typeof elm.namespaceURI !== 'string' || typeof elm.insertBefore !== 'function');
    };

    /**
     * _isNode
     *
     * @param  {Node} obj object to check whether it's a DOM node
     * @return {Boolean} true is object is a DOM node
     */
    var _isNode = function _isNode(object) {
      return (typeof Node === 'undefined' ? 'undefined' : _typeof(Node)) === 'object' ? object instanceof Node : object && (typeof object === 'undefined' ? 'undefined' : _typeof(object)) === 'object' && typeof object.nodeType === 'number' && typeof object.nodeName === 'string';
    };

    /**
     * _executeHook
     * Execute user configurable hooks
     *
     * @param  {String} entryPoint  Name of the hook's entry point
     * @param  {Node} currentNode node to work on with the hook
     * @param  {Object} data additional hook parameters
     */
    var _executeHook = function _executeHook(entryPoint, currentNode, data) {
      if (!hooks[entryPoint]) {
        return;
      }

      arrayForEach(hooks[entryPoint], function (hook) {
        hook.call(DOMPurify, currentNode, data, CONFIG);
      });
    };

    /**
     * _sanitizeElements
     *
     * @protect nodeName
     * @protect textContent
     * @protect removeChild
     *
     * @param   {Node} currentNode to check for permission to exist
     * @return  {Boolean} true if node was killed, false if left alive
     */
    var _sanitizeElements = function _sanitizeElements(currentNode) {
      var content = void 0;

      /* Execute a hook if present */
      _executeHook('beforeSanitizeElements', currentNode, null);

      /* Check if element is clobbered or can clobber */
      if (_isClobbered(currentNode)) {
        _forceRemove(currentNode);
        return true;
      }

      /* Check if tagname contains Unicode */
      if (stringMatch(currentNode.nodeName, /[\u0080-\uFFFF]/)) {
        _forceRemove(currentNode);
        return true;
      }

      /* Now let's check the element's type and name */
      var tagName = transformCaseFunc(currentNode.nodeName);

      /* Execute a hook if present */
      _executeHook('uponSanitizeElement', currentNode, {
        tagName: tagName,
        allowedTags: ALLOWED_TAGS
      });

      /* Detect mXSS attempts abusing namespace confusion */
      if (!_isNode(currentNode.firstElementChild) && (!_isNode(currentNode.content) || !_isNode(currentNode.content.firstElementChild)) && regExpTest(/<[/\w]/g, currentNode.innerHTML) && regExpTest(/<[/\w]/g, currentNode.textContent)) {
        _forceRemove(currentNode);
        return true;
      }

      /* Mitigate a problem with templates inside select */
      if (tagName === 'select' && regExpTest(/<template/i, currentNode.innerHTML)) {
        _forceRemove(currentNode);
        return true;
      }

      /* Remove element if anything forbids its presence */
      if (!ALLOWED_TAGS[tagName] || FORBID_TAGS[tagName]) {
        /* Keep content except for bad-listed elements */
        if (KEEP_CONTENT && !FORBID_CONTENTS[tagName]) {
          var parentNode = getParentNode(currentNode) || currentNode.parentNode;
          var childNodes = getChildNodes(currentNode) || currentNode.childNodes;

          if (childNodes && parentNode) {
            var childCount = childNodes.length;

            for (var i = childCount - 1; i >= 0; --i) {
              parentNode.insertBefore(cloneNode(childNodes[i], true), getNextSibling(currentNode));
            }
          }
        }

        if (!FORBID_TAGS[tagName] && _basicCustomElementTest(tagName)) {
          if (CUSTOM_ELEMENT_HANDLING.tagNameCheck instanceof RegExp && regExpTest(CUSTOM_ELEMENT_HANDLING.tagNameCheck, tagName)) return false;
          if (CUSTOM_ELEMENT_HANDLING.tagNameCheck instanceof Function && CUSTOM_ELEMENT_HANDLING.tagNameCheck(tagName)) return false;
        }

        _forceRemove(currentNode);
        return true;
      }

      /* Check whether element has a valid namespace */
      if (currentNode instanceof Element && !_checkValidNamespace(currentNode)) {
        _forceRemove(currentNode);
        return true;
      }

      if ((tagName === 'noscript' || tagName === 'noembed') && regExpTest(/<\/no(script|embed)/i, currentNode.innerHTML)) {
        _forceRemove(currentNode);
        return true;
      }

      /* Sanitize element content to be template-safe */
      if (SAFE_FOR_TEMPLATES && currentNode.nodeType === 3) {
        /* Get the element's text content */
        content = currentNode.textContent;
        content = stringReplace(content, MUSTACHE_EXPR$$1, ' ');
        content = stringReplace(content, ERB_EXPR$$1, ' ');
        if (currentNode.textContent !== content) {
          arrayPush(DOMPurify.removed, { element: currentNode.cloneNode() });
          currentNode.textContent = content;
        }
      }

      /* Execute a hook if present */
      _executeHook('afterSanitizeElements', currentNode, null);

      return false;
    };

    /**
     * _isValidAttribute
     *
     * @param  {string} lcTag Lowercase tag name of containing element.
     * @param  {string} lcName Lowercase attribute name.
     * @param  {string} value Attribute value.
     * @return {Boolean} Returns true if `value` is valid, otherwise false.
     */
    // eslint-disable-next-line complexity
    var _isValidAttribute = function _isValidAttribute(lcTag, lcName, value) {
      /* Make sure attribute cannot clobber */
      if (SANITIZE_DOM && (lcName === 'id' || lcName === 'name') && (value in document || value in formElement)) {
        return false;
      }

      /* Allow valid data-* attributes: At least one character after "-"
          (https://html.spec.whatwg.org/multipage/dom.html#embedding-custom-non-visible-data-with-the-data-*-attributes)
          XML-compatible (https://html.spec.whatwg.org/multipage/infrastructure.html#xml-compatible and http://www.w3.org/TR/xml/#d0e804)
          We don't need to check the value; it's always URI safe. */
      if (ALLOW_DATA_ATTR && !FORBID_ATTR[lcName] && regExpTest(DATA_ATTR$$1, lcName)) ; else if (ALLOW_ARIA_ATTR && regExpTest(ARIA_ATTR$$1, lcName)) ; else if (!ALLOWED_ATTR[lcName] || FORBID_ATTR[lcName]) {
        if (
        // First condition does a very basic check if a) it's basically a valid custom element tagname AND
        // b) if the tagName passes whatever the user has configured for CUSTOM_ELEMENT_HANDLING.tagNameCheck
        // and c) if the attribute name passes whatever the user has configured for CUSTOM_ELEMENT_HANDLING.attributeNameCheck
        _basicCustomElementTest(lcTag) && (CUSTOM_ELEMENT_HANDLING.tagNameCheck instanceof RegExp && regExpTest(CUSTOM_ELEMENT_HANDLING.tagNameCheck, lcTag) || CUSTOM_ELEMENT_HANDLING.tagNameCheck instanceof Function && CUSTOM_ELEMENT_HANDLING.tagNameCheck(lcTag)) && (CUSTOM_ELEMENT_HANDLING.attributeNameCheck instanceof RegExp && regExpTest(CUSTOM_ELEMENT_HANDLING.attributeNameCheck, lcName) || CUSTOM_ELEMENT_HANDLING.attributeNameCheck instanceof Function && CUSTOM_ELEMENT_HANDLING.attributeNameCheck(lcName)) ||
        // Alternative, second condition checks if it's an `is`-attribute, AND
        // the value passes whatever the user has configured for CUSTOM_ELEMENT_HANDLING.tagNameCheck
        lcName === 'is' && CUSTOM_ELEMENT_HANDLING.allowCustomizedBuiltInElements && (CUSTOM_ELEMENT_HANDLING.tagNameCheck instanceof RegExp && regExpTest(CUSTOM_ELEMENT_HANDLING.tagNameCheck, value) || CUSTOM_ELEMENT_HANDLING.tagNameCheck instanceof Function && CUSTOM_ELEMENT_HANDLING.tagNameCheck(value))) ; else {
          return false;
        }
        /* Check value is safe. First, is attr inert? If so, is safe */
      } else if (URI_SAFE_ATTRIBUTES[lcName]) ; else if (regExpTest(IS_ALLOWED_URI$$1, stringReplace(value, ATTR_WHITESPACE$$1, ''))) ; else if ((lcName === 'src' || lcName === 'xlink:href' || lcName === 'href') && lcTag !== 'script' && stringIndexOf(value, 'data:') === 0 && DATA_URI_TAGS[lcTag]) ; else if (ALLOW_UNKNOWN_PROTOCOLS && !regExpTest(IS_SCRIPT_OR_DATA$$1, stringReplace(value, ATTR_WHITESPACE$$1, ''))) ; else if (!value) ; else {
        return false;
      }

      return true;
    };

    /**
     * _basicCustomElementCheck
     * checks if at least one dash is included in tagName, and it's not the first char
     * for more sophisticated checking see https://github.com/sindresorhus/validate-element-name
     * @param {string} tagName name of the tag of the node to sanitize
     */
    var _basicCustomElementTest = function _basicCustomElementTest(tagName) {
      return tagName.indexOf('-') > 0;
    };

    /**
     * _sanitizeAttributes
     *
     * @protect attributes
     * @protect nodeName
     * @protect removeAttribute
     * @protect setAttribute
     *
     * @param  {Node} currentNode to sanitize
     */
    var _sanitizeAttributes = function _sanitizeAttributes(currentNode) {
      var attr = void 0;
      var value = void 0;
      var lcName = void 0;
      var l = void 0;
      /* Execute a hook if present */
      _executeHook('beforeSanitizeAttributes', currentNode, null);

      var attributes = currentNode.attributes;

      /* Check if we have attributes; if not we might have a text node */

      if (!attributes) {
        return;
      }

      var hookEvent = {
        attrName: '',
        attrValue: '',
        keepAttr: true,
        allowedAttributes: ALLOWED_ATTR
      };
      l = attributes.length;

      /* Go backwards over all attributes; safely remove bad ones */
      while (l--) {
        attr = attributes[l];
        var _attr = attr,
            name = _attr.name,
            namespaceURI = _attr.namespaceURI;

        value = stringTrim(attr.value);
        lcName = transformCaseFunc(name);

        /* Execute a hook if present */
        hookEvent.attrName = lcName;
        hookEvent.attrValue = value;
        hookEvent.keepAttr = true;
        hookEvent.forceKeepAttr = undefined; // Allows developers to see this is a property they can set
        _executeHook('uponSanitizeAttribute', currentNode, hookEvent);
        value = hookEvent.attrValue;
        /* Did the hooks approve of the attribute? */
        if (hookEvent.forceKeepAttr) {
          continue;
        }

        /* Remove attribute */
        _removeAttribute(name, currentNode);

        /* Did the hooks approve of the attribute? */
        if (!hookEvent.keepAttr) {
          continue;
        }

        /* Work around a security issue in jQuery 3.0 */
        if (regExpTest(/\/>/i, value)) {
          _removeAttribute(name, currentNode);
          continue;
        }

        /* Sanitize attribute content to be template-safe */
        if (SAFE_FOR_TEMPLATES) {
          value = stringReplace(value, MUSTACHE_EXPR$$1, ' ');
          value = stringReplace(value, ERB_EXPR$$1, ' ');
        }

        /* Is `value` valid for this attribute? */
        var lcTag = transformCaseFunc(currentNode.nodeName);
        if (!_isValidAttribute(lcTag, lcName, value)) {
          continue;
        }

        /* Handle invalid data-* attribute set by try-catching it */
        try {
          if (namespaceURI) {
            currentNode.setAttributeNS(namespaceURI, name, value);
          } else {
            /* Fallback to setAttribute() for browser-unrecognized namespaces e.g. "x-schema". */
            currentNode.setAttribute(name, value);
          }

          arrayPop(DOMPurify.removed);
        } catch (_) {}
      }

      /* Execute a hook if present */
      _executeHook('afterSanitizeAttributes', currentNode, null);
    };

    /**
     * _sanitizeShadowDOM
     *
     * @param  {DocumentFragment} fragment to iterate over recursively
     */
    var _sanitizeShadowDOM = function _sanitizeShadowDOM(fragment) {
      var shadowNode = void 0;
      var shadowIterator = _createIterator(fragment);

      /* Execute a hook if present */
      _executeHook('beforeSanitizeShadowDOM', fragment, null);

      while (shadowNode = shadowIterator.nextNode()) {
        /* Execute a hook if present */
        _executeHook('uponSanitizeShadowNode', shadowNode, null);

        /* Sanitize tags and elements */
        if (_sanitizeElements(shadowNode)) {
          continue;
        }

        /* Deep shadow DOM detected */
        if (shadowNode.content instanceof DocumentFragment) {
          _sanitizeShadowDOM(shadowNode.content);
        }

        /* Check attributes, sanitize if necessary */
        _sanitizeAttributes(shadowNode);
      }

      /* Execute a hook if present */
      _executeHook('afterSanitizeShadowDOM', fragment, null);
    };

    /**
     * Sanitize
     * Public method providing core sanitation functionality
     *
     * @param {String|Node} dirty string or DOM node
     * @param {Object} configuration object
     */
    // eslint-disable-next-line complexity
    DOMPurify.sanitize = function (dirty, cfg) {
      var body = void 0;
      var importedNode = void 0;
      var currentNode = void 0;
      var oldNode = void 0;
      var returnNode = void 0;
      /* Make sure we have a string to sanitize.
        DO NOT return early, as this will return the wrong type if
        the user has requested a DOM object rather than a string */
      IS_EMPTY_INPUT = !dirty;
      if (IS_EMPTY_INPUT) {
        dirty = '<!-->';
      }

      /* Stringify, in case dirty is an object */
      if (typeof dirty !== 'string' && !_isNode(dirty)) {
        // eslint-disable-next-line no-negated-condition
        if (typeof dirty.toString !== 'function') {
          throw typeErrorCreate('toString is not a function');
        } else {
          dirty = dirty.toString();
          if (typeof dirty !== 'string') {
            throw typeErrorCreate('dirty is not a string, aborting');
          }
        }
      }

      /* Check we can run. Otherwise fall back or ignore */
      if (!DOMPurify.isSupported) {
        if (_typeof(window.toStaticHTML) === 'object' || typeof window.toStaticHTML === 'function') {
          if (typeof dirty === 'string') {
            return window.toStaticHTML(dirty);
          }

          if (_isNode(dirty)) {
            return window.toStaticHTML(dirty.outerHTML);
          }
        }

        return dirty;
      }

      /* Assign config vars */
      if (!SET_CONFIG) {
        _parseConfig(cfg);
      }

      /* Clean up removed elements */
      DOMPurify.removed = [];

      /* Check if dirty is correctly typed for IN_PLACE */
      if (typeof dirty === 'string') {
        IN_PLACE = false;
      }

      if (IN_PLACE) {
        /* Do some early pre-sanitization to avoid unsafe root nodes */
        if (dirty.nodeName) {
          var tagName = transformCaseFunc(dirty.nodeName);
          if (!ALLOWED_TAGS[tagName] || FORBID_TAGS[tagName]) {
            throw typeErrorCreate('root node is forbidden and cannot be sanitized in-place');
          }
        }
      } else if (dirty instanceof Node) {
        /* If dirty is a DOM element, append to an empty document to avoid
           elements being stripped by the parser */
        body = _initDocument('<!---->');
        importedNode = body.ownerDocument.importNode(dirty, true);
        if (importedNode.nodeType === 1 && importedNode.nodeName === 'BODY') {
          /* Node is already a body, use as is */
          body = importedNode;
        } else if (importedNode.nodeName === 'HTML') {
          body = importedNode;
        } else {
          // eslint-disable-next-line unicorn/prefer-dom-node-append
          body.appendChild(importedNode);
        }
      } else {
        /* Exit directly if we have nothing to do */
        if (!RETURN_DOM && !SAFE_FOR_TEMPLATES && !WHOLE_DOCUMENT &&
        // eslint-disable-next-line unicorn/prefer-includes
        dirty.indexOf('<') === -1) {
          return trustedTypesPolicy && RETURN_TRUSTED_TYPE ? trustedTypesPolicy.createHTML(dirty) : dirty;
        }

        /* Initialize the document to work on */
        body = _initDocument(dirty);

        /* Check we have a DOM node from the data */
        if (!body) {
          return RETURN_DOM ? null : RETURN_TRUSTED_TYPE ? emptyHTML : '';
        }
      }

      /* Remove first element node (ours) if FORCE_BODY is set */
      if (body && FORCE_BODY) {
        _forceRemove(body.firstChild);
      }

      /* Get node iterator */
      var nodeIterator = _createIterator(IN_PLACE ? dirty : body);

      /* Now start iterating over the created document */
      while (currentNode = nodeIterator.nextNode()) {
        /* Fix IE's strange behavior with manipulated textNodes #89 */
        if (currentNode.nodeType === 3 && currentNode === oldNode) {
          continue;
        }

        /* Sanitize tags and elements */
        if (_sanitizeElements(currentNode)) {
          continue;
        }

        /* Shadow DOM detected, sanitize it */
        if (currentNode.content instanceof DocumentFragment) {
          _sanitizeShadowDOM(currentNode.content);
        }

        /* Check attributes, sanitize if necessary */
        _sanitizeAttributes(currentNode);

        oldNode = currentNode;
      }

      oldNode = null;

      /* If we sanitized `dirty` in-place, return it. */
      if (IN_PLACE) {
        return dirty;
      }

      /* Return sanitized string or DOM */
      if (RETURN_DOM) {
        if (RETURN_DOM_FRAGMENT) {
          returnNode = createDocumentFragment.call(body.ownerDocument);

          while (body.firstChild) {
            // eslint-disable-next-line unicorn/prefer-dom-node-append
            returnNode.appendChild(body.firstChild);
          }
        } else {
          returnNode = body;
        }

        if (ALLOWED_ATTR.shadowroot) {
          /*
            AdoptNode() is not used because internal state is not reset
            (e.g. the past names map of a HTMLFormElement), this is safe
            in theory but we would rather not risk another attack vector.
            The state that is cloned by importNode() is explicitly defined
            by the specs.
          */
          returnNode = importNode.call(originalDocument, returnNode, true);
        }

        return returnNode;
      }

      var serializedHTML = WHOLE_DOCUMENT ? body.outerHTML : body.innerHTML;

      /* Sanitize final string template-safe */
      if (SAFE_FOR_TEMPLATES) {
        serializedHTML = stringReplace(serializedHTML, MUSTACHE_EXPR$$1, ' ');
        serializedHTML = stringReplace(serializedHTML, ERB_EXPR$$1, ' ');
      }

      return trustedTypesPolicy && RETURN_TRUSTED_TYPE ? trustedTypesPolicy.createHTML(serializedHTML) : serializedHTML;
    };

    /**
     * Public method to set the configuration once
     * setConfig
     *
     * @param {Object} cfg configuration object
     */
    DOMPurify.setConfig = function (cfg) {
      _parseConfig(cfg);
      SET_CONFIG = true;
    };

    /**
     * Public method to remove the configuration
     * clearConfig
     *
     */
    DOMPurify.clearConfig = function () {
      CONFIG = null;
      SET_CONFIG = false;
    };

    /**
     * Public method to check if an attribute value is valid.
     * Uses last set config, if any. Otherwise, uses config defaults.
     * isValidAttribute
     *
     * @param  {string} tag Tag name of containing element.
     * @param  {string} attr Attribute name.
     * @param  {string} value Attribute value.
     * @return {Boolean} Returns true if `value` is valid. Otherwise, returns false.
     */
    DOMPurify.isValidAttribute = function (tag, attr, value) {
      /* Initialize shared config vars if necessary. */
      if (!CONFIG) {
        _parseConfig({});
      }

      var lcTag = transformCaseFunc(tag);
      var lcName = transformCaseFunc(attr);
      return _isValidAttribute(lcTag, lcName, value);
    };

    /**
     * AddHook
     * Public method to add DOMPurify hooks
     *
     * @param {String} entryPoint entry point for the hook to add
     * @param {Function} hookFunction function to execute
     */
    DOMPurify.addHook = function (entryPoint, hookFunction) {
      if (typeof hookFunction !== 'function') {
        return;
      }

      hooks[entryPoint] = hooks[entryPoint] || [];
      arrayPush(hooks[entryPoint], hookFunction);
    };

    /**
     * RemoveHook
     * Public method to remove a DOMPurify hook at a given entryPoint
     * (pops it from the stack of hooks if more are present)
     *
     * @param {String} entryPoint entry point for the hook to remove
     */
    DOMPurify.removeHook = function (entryPoint) {
      if (hooks[entryPoint]) {
        arrayPop(hooks[entryPoint]);
      }
    };

    /**
     * RemoveHooks
     * Public method to remove all DOMPurify hooks at a given entryPoint
     *
     * @param  {String} entryPoint entry point for the hooks to remove
     */
    DOMPurify.removeHooks = function (entryPoint) {
      if (hooks[entryPoint]) {
        hooks[entryPoint] = [];
      }
    };

    /**
     * RemoveAllHooks
     * Public method to remove all DOMPurify hooks
     *
     */
    DOMPurify.removeAllHooks = function () {
      hooks = {};
    };

    return DOMPurify;
  }

  var purify = createDOMPurify();

  return purify;

}));


},{}],6:[function(require,module,exports){
// shim for using process in browser
var process = module.exports = {};

// cached from whatever global is present so that test runners that stub it
// don't break things.  But we need to wrap it in a try catch in case it is
// wrapped in strict mode code which doesn't define any globals.  It's inside a
// function because try/catches deoptimize in certain engines.

var cachedSetTimeout;
var cachedClearTimeout;

function defaultSetTimout() {
    throw new Error('setTimeout has not been defined');
}
function defaultClearTimeout () {
    throw new Error('clearTimeout has not been defined');
}
(function () {
    try {
        if (typeof setTimeout === 'function') {
            cachedSetTimeout = setTimeout;
        } else {
            cachedSetTimeout = defaultSetTimout;
        }
    } catch (e) {
        cachedSetTimeout = defaultSetTimout;
    }
    try {
        if (typeof clearTimeout === 'function') {
            cachedClearTimeout = clearTimeout;
        } else {
            cachedClearTimeout = defaultClearTimeout;
        }
    } catch (e) {
        cachedClearTimeout = defaultClearTimeout;
    }
} ())
function runTimeout(fun) {
    if (cachedSetTimeout === setTimeout) {
        //normal enviroments in sane situations
        return setTimeout(fun, 0);
    }
    // if setTimeout wasn't available but was latter defined
    if ((cachedSetTimeout === defaultSetTimout || !cachedSetTimeout) && setTimeout) {
        cachedSetTimeout = setTimeout;
        return setTimeout(fun, 0);
    }
    try {
        // when when somebody has screwed with setTimeout but no I.E. maddness
        return cachedSetTimeout(fun, 0);
    } catch(e){
        try {
            // When we are in I.E. but the script has been evaled so I.E. doesn't trust the global object when called normally
            return cachedSetTimeout.call(null, fun, 0);
        } catch(e){
            // same as above but when it's a version of I.E. that must have the global object for 'this', hopfully our context correct otherwise it will throw a global error
            return cachedSetTimeout.call(this, fun, 0);
        }
    }


}
function runClearTimeout(marker) {
    if (cachedClearTimeout === clearTimeout) {
        //normal enviroments in sane situations
        return clearTimeout(marker);
    }
    // if clearTimeout wasn't available but was latter defined
    if ((cachedClearTimeout === defaultClearTimeout || !cachedClearTimeout) && clearTimeout) {
        cachedClearTimeout = clearTimeout;
        return clearTimeout(marker);
    }
    try {
        // when when somebody has screwed with setTimeout but no I.E. maddness
        return cachedClearTimeout(marker);
    } catch (e){
        try {
            // When we are in I.E. but the script has been evaled so I.E. doesn't  trust the global object when called normally
            return cachedClearTimeout.call(null, marker);
        } catch (e){
            // same as above but when it's a version of I.E. that must have the global object for 'this', hopfully our context correct otherwise it will throw a global error.
            // Some versions of I.E. have different rules for clearTimeout vs setTimeout
            return cachedClearTimeout.call(this, marker);
        }
    }



}
var queue = [];
var draining = false;
var currentQueue;
var queueIndex = -1;

function cleanUpNextTick() {
    if (!draining || !currentQueue) {
        return;
    }
    draining = false;
    if (currentQueue.length) {
        queue = currentQueue.concat(queue);
    } else {
        queueIndex = -1;
    }
    if (queue.length) {
        drainQueue();
    }
}

function drainQueue() {
    if (draining) {
        return;
    }
    var timeout = runTimeout(cleanUpNextTick);
    draining = true;

    var len = queue.length;
    while(len) {
        currentQueue = queue;
        queue = [];
        while (++queueIndex < len) {
            if (currentQueue) {
                currentQueue[queueIndex].run();
            }
        }
        queueIndex = -1;
        len = queue.length;
    }
    currentQueue = null;
    draining = false;
    runClearTimeout(timeout);
}

process.nextTick = function (fun) {
    var args = new Array(arguments.length - 1);
    if (arguments.length > 1) {
        for (var i = 1; i < arguments.length; i++) {
            args[i - 1] = arguments[i];
        }
    }
    queue.push(new Item(fun, args));
    if (queue.length === 1 && !draining) {
        runTimeout(drainQueue);
    }
};

// v8 likes predictible objects
function Item(fun, array) {
    this.fun = fun;
    this.array = array;
}
Item.prototype.run = function () {
    this.fun.apply(null, this.array);
};
process.title = 'browser';
process.browser = true;
process.env = {};
process.argv = [];
process.version = ''; // empty string to avoid regexp issues
process.versions = {};

function noop() {}

process.on = noop;
process.addListener = noop;
process.once = noop;
process.off = noop;
process.removeListener = noop;
process.removeAllListeners = noop;
process.emit = noop;
process.prependListener = noop;
process.prependOnceListener = noop;

process.listeners = function (name) { return [] }

process.binding = function (name) {
    throw new Error('process.binding is not supported');
};

process.cwd = function () { return '/' };
process.chdir = function (dir) {
    throw new Error('process.chdir is not supported');
};
process.umask = function() { return 0; };

},{}],7:[function(require,module,exports){
(function (global){(function (){
/*! *****************************************************************************
Copyright (c) Microsoft Corporation.

Permission to use, copy, modify, and/or distribute this software for any
purpose with or without fee is hereby granted.

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
PERFORMANCE OF THIS SOFTWARE.
***************************************************************************** */
/* global global, define, System, Reflect, Promise */
var __extends;
var __assign;
var __rest;
var __decorate;
var __param;
var __metadata;
var __awaiter;
var __generator;
var __exportStar;
var __values;
var __read;
var __spread;
var __spreadArrays;
var __spreadArray;
var __await;
var __asyncGenerator;
var __asyncDelegator;
var __asyncValues;
var __makeTemplateObject;
var __importStar;
var __importDefault;
var __classPrivateFieldGet;
var __classPrivateFieldSet;
var __createBinding;
(function (factory) {
    var root = typeof global === "object" ? global : typeof self === "object" ? self : typeof this === "object" ? this : {};
    if (typeof define === "function" && define.amd) {
        define("tslib", ["exports"], function (exports) { factory(createExporter(root, createExporter(exports))); });
    }
    else if (typeof module === "object" && typeof module.exports === "object") {
        factory(createExporter(root, createExporter(module.exports)));
    }
    else {
        factory(createExporter(root));
    }
    function createExporter(exports, previous) {
        if (exports !== root) {
            if (typeof Object.create === "function") {
                Object.defineProperty(exports, "__esModule", { value: true });
            }
            else {
                exports.__esModule = true;
            }
        }
        return function (id, v) { return exports[id] = previous ? previous(id, v) : v; };
    }
})
(function (exporter) {
    var extendStatics = Object.setPrototypeOf ||
        ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
        function (d, b) { for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p]; };

    __extends = function (d, b) {
        if (typeof b !== "function" && b !== null)
            throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };

    __assign = Object.assign || function (t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p)) t[p] = s[p];
        }
        return t;
    };

    __rest = function (s, e) {
        var t = {};
        for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p) && e.indexOf(p) < 0)
            t[p] = s[p];
        if (s != null && typeof Object.getOwnPropertySymbols === "function")
            for (var i = 0, p = Object.getOwnPropertySymbols(s); i < p.length; i++) {
                if (e.indexOf(p[i]) < 0 && Object.prototype.propertyIsEnumerable.call(s, p[i]))
                    t[p[i]] = s[p[i]];
            }
        return t;
    };

    __decorate = function (decorators, target, key, desc) {
        var c = arguments.length, r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc, d;
        if (typeof Reflect === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);
        else for (var i = decorators.length - 1; i >= 0; i--) if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
        return c > 3 && r && Object.defineProperty(target, key, r), r;
    };

    __param = function (paramIndex, decorator) {
        return function (target, key) { decorator(target, key, paramIndex); }
    };

    __metadata = function (metadataKey, metadataValue) {
        if (typeof Reflect === "object" && typeof Reflect.metadata === "function") return Reflect.metadata(metadataKey, metadataValue);
    };

    __awaiter = function (thisArg, _arguments, P, generator) {
        function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
        return new (P || (P = Promise))(function (resolve, reject) {
            function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
            function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
            function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
            step((generator = generator.apply(thisArg, _arguments || [])).next());
        });
    };

    __generator = function (thisArg, body) {
        var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g;
        return g = { next: verb(0), "throw": verb(1), "return": verb(2) }, typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
        function verb(n) { return function (v) { return step([n, v]); }; }
        function step(op) {
            if (f) throw new TypeError("Generator is already executing.");
            while (_) try {
                if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
                if (y = 0, t) op = [op[0] & 2, t.value];
                switch (op[0]) {
                    case 0: case 1: t = op; break;
                    case 4: _.label++; return { value: op[1], done: false };
                    case 5: _.label++; y = op[1]; op = [0]; continue;
                    case 7: op = _.ops.pop(); _.trys.pop(); continue;
                    default:
                        if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                        if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                        if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                        if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                        if (t[2]) _.ops.pop();
                        _.trys.pop(); continue;
                }
                op = body.call(thisArg, _);
            } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
            if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
        }
    };

    __exportStar = function(m, o) {
        for (var p in m) if (p !== "default" && !Object.prototype.hasOwnProperty.call(o, p)) __createBinding(o, m, p);
    };

    __createBinding = Object.create ? (function(o, m, k, k2) {
        if (k2 === undefined) k2 = k;
        Object.defineProperty(o, k2, { enumerable: true, get: function() { return m[k]; } });
    }) : (function(o, m, k, k2) {
        if (k2 === undefined) k2 = k;
        o[k2] = m[k];
    });

    __values = function (o) {
        var s = typeof Symbol === "function" && Symbol.iterator, m = s && o[s], i = 0;
        if (m) return m.call(o);
        if (o && typeof o.length === "number") return {
            next: function () {
                if (o && i >= o.length) o = void 0;
                return { value: o && o[i++], done: !o };
            }
        };
        throw new TypeError(s ? "Object is not iterable." : "Symbol.iterator is not defined.");
    };

    __read = function (o, n) {
        var m = typeof Symbol === "function" && o[Symbol.iterator];
        if (!m) return o;
        var i = m.call(o), r, ar = [], e;
        try {
            while ((n === void 0 || n-- > 0) && !(r = i.next()).done) ar.push(r.value);
        }
        catch (error) { e = { error: error }; }
        finally {
            try {
                if (r && !r.done && (m = i["return"])) m.call(i);
            }
            finally { if (e) throw e.error; }
        }
        return ar;
    };

    /** @deprecated */
    __spread = function () {
        for (var ar = [], i = 0; i < arguments.length; i++)
            ar = ar.concat(__read(arguments[i]));
        return ar;
    };

    /** @deprecated */
    __spreadArrays = function () {
        for (var s = 0, i = 0, il = arguments.length; i < il; i++) s += arguments[i].length;
        for (var r = Array(s), k = 0, i = 0; i < il; i++)
            for (var a = arguments[i], j = 0, jl = a.length; j < jl; j++, k++)
                r[k] = a[j];
        return r;
    };

    __spreadArray = function (to, from, pack) {
        if (pack || arguments.length === 2) for (var i = 0, l = from.length, ar; i < l; i++) {
            if (ar || !(i in from)) {
                if (!ar) ar = Array.prototype.slice.call(from, 0, i);
                ar[i] = from[i];
            }
        }
        return to.concat(ar || Array.prototype.slice.call(from));
    };

    __await = function (v) {
        return this instanceof __await ? (this.v = v, this) : new __await(v);
    };

    __asyncGenerator = function (thisArg, _arguments, generator) {
        if (!Symbol.asyncIterator) throw new TypeError("Symbol.asyncIterator is not defined.");
        var g = generator.apply(thisArg, _arguments || []), i, q = [];
        return i = {}, verb("next"), verb("throw"), verb("return"), i[Symbol.asyncIterator] = function () { return this; }, i;
        function verb(n) { if (g[n]) i[n] = function (v) { return new Promise(function (a, b) { q.push([n, v, a, b]) > 1 || resume(n, v); }); }; }
        function resume(n, v) { try { step(g[n](v)); } catch (e) { settle(q[0][3], e); } }
        function step(r) { r.value instanceof __await ? Promise.resolve(r.value.v).then(fulfill, reject) : settle(q[0][2], r);  }
        function fulfill(value) { resume("next", value); }
        function reject(value) { resume("throw", value); }
        function settle(f, v) { if (f(v), q.shift(), q.length) resume(q[0][0], q[0][1]); }
    };

    __asyncDelegator = function (o) {
        var i, p;
        return i = {}, verb("next"), verb("throw", function (e) { throw e; }), verb("return"), i[Symbol.iterator] = function () { return this; }, i;
        function verb(n, f) { i[n] = o[n] ? function (v) { return (p = !p) ? { value: __await(o[n](v)), done: n === "return" } : f ? f(v) : v; } : f; }
    };

    __asyncValues = function (o) {
        if (!Symbol.asyncIterator) throw new TypeError("Symbol.asyncIterator is not defined.");
        var m = o[Symbol.asyncIterator], i;
        return m ? m.call(o) : (o = typeof __values === "function" ? __values(o) : o[Symbol.iterator](), i = {}, verb("next"), verb("throw"), verb("return"), i[Symbol.asyncIterator] = function () { return this; }, i);
        function verb(n) { i[n] = o[n] && function (v) { return new Promise(function (resolve, reject) { v = o[n](v), settle(resolve, reject, v.done, v.value); }); }; }
        function settle(resolve, reject, d, v) { Promise.resolve(v).then(function(v) { resolve({ value: v, done: d }); }, reject); }
    };

    __makeTemplateObject = function (cooked, raw) {
        if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
        return cooked;
    };

    var __setModuleDefault = Object.create ? (function(o, v) {
        Object.defineProperty(o, "default", { enumerable: true, value: v });
    }) : function(o, v) {
        o["default"] = v;
    };

    __importStar = function (mod) {
        if (mod && mod.__esModule) return mod;
        var result = {};
        if (mod != null) for (var k in mod) if (k !== "default" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
        __setModuleDefault(result, mod);
        return result;
    };

    __importDefault = function (mod) {
        return (mod && mod.__esModule) ? mod : { "default": mod };
    };

    __classPrivateFieldGet = function (receiver, state, kind, f) {
        if (kind === "a" && !f) throw new TypeError("Private accessor was defined without a getter");
        if (typeof state === "function" ? receiver !== state || !f : !state.has(receiver)) throw new TypeError("Cannot read private member from an object whose class did not declare it");
        return kind === "m" ? f : kind === "a" ? f.call(receiver) : f ? f.value : state.get(receiver);
    };

    __classPrivateFieldSet = function (receiver, state, value, kind, f) {
        if (kind === "m") throw new TypeError("Private method is not writable");
        if (kind === "a" && !f) throw new TypeError("Private accessor was defined without a setter");
        if (typeof state === "function" ? receiver !== state || !f : !state.has(receiver)) throw new TypeError("Cannot write private member to an object whose class did not declare it");
        return (kind === "a" ? f.call(receiver, value) : f ? f.value = value : state.set(receiver, value)), value;
    };

    exporter("__extends", __extends);
    exporter("__assign", __assign);
    exporter("__rest", __rest);
    exporter("__decorate", __decorate);
    exporter("__param", __param);
    exporter("__metadata", __metadata);
    exporter("__awaiter", __awaiter);
    exporter("__generator", __generator);
    exporter("__exportStar", __exportStar);
    exporter("__createBinding", __createBinding);
    exporter("__values", __values);
    exporter("__read", __read);
    exporter("__spread", __spread);
    exporter("__spreadArrays", __spreadArrays);
    exporter("__spreadArray", __spreadArray);
    exporter("__await", __await);
    exporter("__asyncGenerator", __asyncGenerator);
    exporter("__asyncDelegator", __asyncDelegator);
    exporter("__asyncValues", __asyncValues);
    exporter("__makeTemplateObject", __makeTemplateObject);
    exporter("__importStar", __importStar);
    exporter("__importDefault", __importDefault);
    exporter("__classPrivateFieldGet", __classPrivateFieldGet);
    exporter("__classPrivateFieldSet", __classPrivateFieldSet);
});

}).call(this)}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})

},{}],8:[function(require,module,exports){
"use strict";

var _url = require("../../mixins/url");

var _url2 = _interopRequireDefault(_url);

var _constants = require("../../constants");

var _constants2 = _interopRequireDefault(_constants);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

Vue.component("item-list-sorting", {
    mixins: [_url2.default],

    delimiters: ["${", "}"],

    props: ["sortingList", "defaultSorting", "template"],

    data: function data() {
        return {
            selectedSorting: {}
        };
    },
    created: function created() {
        this.$options.template = this.template || "#vue-item-list-sorting";
        this.setSelectedValue();
    },


    methods: {
        updateSorting: function updateSorting() {
            this.setUrlParamValues([{
                key: _constants2.default.PARAMETER_SORTING,
                value: this.selectedSorting
            }, {
                key: _constants2.default.PARAMETER_PAGE,
                value: 1
            }]);
        },


        /**
         * Determine the initial value and set it in the vuex storage.
         */
        setSelectedValue: function setSelectedValue() {
            var urlParams = this.getUrlParams(document.location.search);

            if (urlParams.sorting) {
                this.selectedSorting = urlParams.sorting;
            } else {
                this.selectedSorting = this.defaultSorting;
            }

            this.$store.commit("setItemListSorting", this.selectedSorting);
        }
    }
});

},{"../../constants":21,"../../mixins/url":24}],9:[function(require,module,exports){
"use strict";

var _url = require("../../mixins/url");

var _url2 = _interopRequireDefault(_url);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

Vue.component("item-search", {
    mixins: [_url2.default],

    props: {
        template: {
            type: String,
            default: "#vue-item-search"
        },
        showItemImages: {
            type: Boolean,
            default: false
        },
        forwardToSingleItem: {
            type: Boolean,
            default: App.config.search.forwardToSingleItem
        }
    },

    data: function data() {
        return {
            promiseCount: 0,
            autocompleteResult: [],
            selectedAutocompleteIndex: -1,
            isSearchFocused: false
        };
    },


    computed: {
        selectedAutocompleteItem: function selectedAutocompleteItem() {
            return null;
        }
    },

    created: function created() {
        this.$options.template = this.template;
    },
    mounted: function mounted() {
        var _this = this;

        this.$nextTick(function () {
            var urlParams = _this.getUrlParams(document.location.search);

            _this.$store.commit("setItemListSearchString", urlParams.query);

            var rawQuery = urlParams.query ? urlParams.query : '';
            // Manually regex out all "+" signs as decodeURIComponent does not take care of that.
            // If we wouldn't replace them with spaces, "+" signs would be displayed in the search field.
            _this.$refs.searchInput.value = decodeURIComponent(rawQuery.replace(/\+/g, ' '));
        });
    },


    methods: {
        search: function search() {
            $('#searchBox').collapse('hide');
        },
        autocomplete: function autocomplete(searchString) {},
        selectAutocompleteItem: function selectAutocompleteItem(item) {},
        keyup: function keyup() {},
        keydown: function keydown() {},


        // hide autocomplete after 100ms to make clicking on it possible
        setIsSearchFocused: function setIsSearchFocused(value) {
            var _this2 = this;

            setTimeout(function () {
                _this2.isSearchFocused = !!value;
            }, 100);
        }
    }
});

},{"../../mixins/url":24}],10:[function(require,module,exports){
"use strict";

var _url = require("../../mixins/url");

var _url2 = _interopRequireDefault(_url);

var _constants = require("../../constants");

var _constants2 = _interopRequireDefault(_constants);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

Vue.component("items-per-page", {
    mixins: [_url2.default],

    delimiters: ["${", "}"],

    props: ["paginationValues", "template"],

    data: function data() {
        return {
            selectedValue: null
        };
    },
    created: function created() {
        this.$options.template = this.template || "#vue-items-per-page";
        this.setSelectedValueByUrl();
    },


    methods: {
        itemsPerPageChanged: function itemsPerPageChanged() {
            this.setUrlParamValues([{
                key: _constants2.default.PARAMETER_ITEMS,
                value: this.selectedValue
            }, {
                key: _constants2.default.PARAMETER_PAGE,
                value: 1
            }]);
        },
        setSelectedValueByUrl: function setSelectedValueByUrl() {
            var urlParams = this.getUrlParams(document.location.search);
            var defaultItemsPerPage = App.config.pagination.columnsPerPage * App.config.pagination.rowsPerPage[0];

            if (urlParams.items) {
                if (this.paginationValues.includes(parseInt(urlParams.items))) {
                    this.selectedValue = urlParams.items;
                } else {
                    this.selectedValue = defaultItemsPerPage;
                }
            } else {
                this.selectedValue = defaultItemsPerPage;
            }

            this.$store.commit("setItemsPerPage", parseInt(this.selectedValue));
        }
    }
});

},{"../../constants":21,"../../mixins/url":24}],11:[function(require,module,exports){
"use strict";

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

var _url = require("../../mixins/url");

var _url2 = _interopRequireDefault(_url);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var options = {
    mixins: [_url2.default],

    delimiters: ["${", "}"],

    props: ["template"],

    data: function data() {
        return {
            lastPageMax: 0
        };
    },


    computed: _extends({
        pageMax: function pageMax() {
            if (this.isLoading) {
                return this.lastPageMax;
            }

            var pageMax = this.totalItems / parseInt(this.itemsPerPage);

            if (this.totalItems % parseInt(this.itemsPerPage) > 0) {
                pageMax += 1;
            }

            this.lastPageMax = parseInt(pageMax) || 1;

            return parseInt(pageMax) || 1;
        }
    }, Vuex.mapState({
        page: function page(state) {
            return state.itemList.page || 1;
        },
        isLoading: function isLoading(state) {
            return state.itemList.isLoading;
        },
        itemsPerPage: function itemsPerPage(state) {
            return state.itemList.itemsPerPage;
        },
        totalItems: function totalItems(state) {
            return state.itemList.totalItems;
        }
    })),

    created: function created() {
        this.$options.template = this.template;

        var urlParams = this.getUrlParams(document.location.search);
        var page = urlParams.page || 1;

        this.$store.commit("setItemListPage", parseInt(page));
    },


    methods: {
        setPage: function setPage(page) {
            this.setUrlParamValue('page', page);
        }
    }
};

Vue.component('pagination', options);
Vue.component('custom-pagination', options);

},{"../../mixins/url":24}],12:[function(require,module,exports){
"use strict";

var _url = require("../../../mixins/url");

var _url2 = _interopRequireDefault(_url);

var _baseDropdown = require("../../../mixins/baseDropdown");

var _baseDropdown2 = _interopRequireDefault(_baseDropdown);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

Vue.component("item-category-dropdown", {
    mixins: [_url2.default, _baseDropdown2.default],

    computed: {
        dropdownLabel: function dropdownLabel() {
            var selectedFilters = this.getSelectedFilters();
            var label = null;

            for (var i = 0; i < selectedFilters.length; i++) {
                var facet = selectedFilters[i];

                if (facet.id === this.facet.id) {
                    label = facet.name;
                    break;
                }
            }

            return label;
        }
    },

    methods: {
        getSubCategoryName: function getSubCategoryName(parentCategory, subCategory) {
            return parentCategory.name + '_' + subCategory.name;
        }
    }
});

},{"../../../mixins/baseDropdown":23,"../../../mixins/url":24}],13:[function(require,module,exports){
'use strict';

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

var _url = require('../../../mixins/url');

var _url2 = _interopRequireDefault(_url);

var _svgInjector = require('@tanem/svg-injector');

var _dompurify = require('dompurify');

var _dompurify2 = _interopRequireDefault(_dompurify);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

Vue.component("item-color-tiles", {
    mixins: [_url2.default],

    props: ["template", "facet", "fallbackImage"],

    created: function created() {
        this.$options.template = this.template || "#vue-item-color-tiles";
    },


    computed: _extends({}, Vuex.mapState({
        isLoading: function isLoading(state) {
            return state.itemList.isLoading;
        }
    })),

    mounted: function mounted() {
        this.$nextTick(function () {
            (0, _svgInjector.SVGInjector)(document.getElementsByClassName('fl-svg'), {
                beforeEach: function beforeEach(svg) {
                    _dompurify2.default.sanitize(svg, {
                        IN_PLACE: true,
                        USE_PROFILES: { svg: true, svgFilters: true }
                    });
                }
            });
        });
    },


    methods: {
        isSelected: function isSelected(facetValueName) {
            var facetValue = this.facet.values.filter(function (value) {
                return value.name === facetValueName;
            });

            return facetValue.length && this.isValueSelected(this.facet.id, facetValue[0].name);
        },


        tileClicked: function tileClicked(value) {
            this.updateSelectedFilters(this.facet.id, value);
        },

        handleImageError: function handleImageError(event, colorValue) {
            if (!colorValue.hexValue) {
                event.target.src = this.fallbackImage;
            } else {
                event.target.remove();
            }
        }
    }
});

},{"../../../mixins/url":24,"@tanem/svg-injector":1,"dompurify":5}],14:[function(require,module,exports){
'use strict';

var _url = require('../../../mixins/url');

var _url2 = _interopRequireDefault(_url);

var _baseDropdown = require('../../../mixins/baseDropdown');

var _baseDropdown2 = _interopRequireDefault(_baseDropdown);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

Vue.component("item-dropdown", {
    mixins: [_url2.default, _baseDropdown2.default]
});

},{"../../../mixins/baseDropdown":23,"../../../mixins/url":24}],15:[function(require,module,exports){
"use strict";

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

var _url = require("../../../mixins/url");

var _url2 = _interopRequireDefault(_url);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

Vue.component("findologic-item-filter", {
    mixins: [_url2.default],

    delimiters: ["${", "}"],

    props: ["template", "facet"],

    data: function data() {
        return {
            facetType: null
        };
    },


    computed: _extends({
        facets: function facets() {
            return this.facet.values.sort(function (facetA, facetB) {
                if (facetA.position > facetB.position) {
                    return 1;
                } else if (facetA.position < facetB.position) {
                    return -1;
                } else {
                    return 0;
                }
            });
        },
        isSelected: function isSelected() {
            var _this = this;

            return typeof this.getSelectedFilters().find(function (element) {
                return element.id == _this.facet.id;
            }) !== 'undefined';
        }
    }, Vuex.mapState({
        selectedFacets: function selectedFacets(state) {
            return state.itemList.selectedFacets;
        },
        isLoading: function isLoading(state) {
            return state.itemList.isLoading;
        }
    }), {
        selectedValuesCount: function selectedValuesCount() {
            var selectedFacets = this.facet.values.filter(function (value) {
                return value.selected;
            });

            return selectedFacets.length;
        }
    }),

    created: function created() {
        this.$options.template = this.template || "#vue-item-filter";
        this.facetType = typeof this.facet.findologicFilterType !== 'undefined' ? this.facet.findologicFilterType : this.facet.type;
    },


    methods: {
        updateFacet: function updateFacet(facetValue) {
            this.updateSelectedFilters(this.facet.id, facetValue.name);
        },
        getSubCategoryValue: function getSubCategoryValue(parentCategory, subCategory) {
            return {
                id: subCategory.id,
                name: parentCategory.name + '_' + subCategory.name
            };
        }
    }
});

},{"../../../mixins/url":24}],16:[function(require,module,exports){
"use strict";

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

var _url = require("../../../mixins/url");

var _url2 = _interopRequireDefault(_url);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

Vue.component("item-filter-image", {
    mixins: [_url2.default],

    props: ["template", "facet", "fallbackImage"],

    created: function created() {
        this.$options.template = this.template || "#vue-findologic-item-filter-image";
    },


    computed: _extends({}, Vuex.mapState({
        isLoading: function isLoading(state) {
            return state.itemList.isLoading;
        }
    })),

    methods: {
        updateFacet: function updateFacet(facetValue) {
            this.updateSelectedFilters(this.facet.id, facetValue.name);
        },
        handleImageError: function handleImageError(event) {
            event.target.src = this.fallbackImage;
        }
    }
});

},{"../../../mixins/url":24}],17:[function(require,module,exports){
"use strict";

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

Vue.component("findologic-item-filter-list", {
    props: {
        facetData: {
            type: Array,
            default: function _default() {
                return [];
            }
        },
        allowedFacetsTypes: {
            type: Array,
            default: function _default() {
                return [];
            }
        },
        showSelectedFiltersCount: {
            type: Boolean,
            default: false
        }
    },

    computed: _extends({}, Vuex.mapState({
        facets: function facets(state) {
            var _this = this;

            if (!this.allowedFacetsTypes.length) {
                return state.itemList.facets;
            }

            return state.itemList.facets.filter(function (facet) {
                return _this.allowedFacetsTypes.includes(facet.id) || _this.allowedFacetsTypes.includes(facet.type);
            });
        },

        isLoading: function isLoading(state) {
            return state.itemList.isLoading;
        },
        selectedFacets: function selectedFacets(state) {
            return state.itemList.selectedFacets;
        }
    })),

    created: function created() {
        this.$store.commit("addFacets", this.facetData);
    }
});

},{}],18:[function(require,module,exports){
"use strict";

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

var _url = require("../../../mixins/url");

var _url2 = _interopRequireDefault(_url);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

Vue.component("item-filter-price", {
    mixins: [_url2.default],

    delimiters: ["${", "}"],

    props: ["template", "facet"],

    data: function data() {
        return {
            priceMin: "",
            priceMax: "",
            currency: App.activeCurrency
        };
    },
    created: function created() {
        this.$options.template = this.template || "#vue-item-filter-price";

        var values = this.getSelectedFilterValue(this.facet.id);

        this.priceMin = values ? values.min : "";
        this.priceMax = values ? values.max : "";
    },


    computed: _extends({
        isDisabled: function isDisabled() {
            return this.priceMin === "" && this.priceMax === "" || parseFloat(this.priceMin) > parseFloat(this.priceMax) || this.isLoading;
        }
    }, Vuex.mapState({
        isLoading: function isLoading(state) {
            return state.itemList.isLoading;
        }
    })),

    methods: {
        selectAll: function selectAll(event) {
            event.target.select();
        },
        triggerFilter: function triggerFilter() {
            if (!this.isDisabled) {
                var facetValue = {
                    min: this.priceMin,
                    max: this.priceMax ? this.priceMax : Number.MAX_SAFE_INTEGER
                };

                this.updateSelectedFilters(this.facet.id, facetValue);
            }
        }
    }
});

},{"../../../mixins/url":24}],19:[function(require,module,exports){
"use strict";

var _url = require("../../../mixins/url");

var _url2 = _interopRequireDefault(_url);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

Vue.component("item-filter-tag-list", {
    mixins: [_url2.default],

    delimiters: ["${", "}"],

    props: {
        template: {
            type: String,
            default: "#vue-item-filter-tag-list"
        },
        marginClasses: {
            type: String,
            default: null
        },
        marginInlineStyles: {
            type: String,
            default: null
        }
    },

    computed: {
        tagList: function tagList() {
            return this.getSelectedFilters();
        },
        facetInfo: function facetInfo() {
            return this.getFacetIdInfoMap();
        }
    },

    created: function created() {
        this.$options.template = this.template || "#vue-item-filter-tag-list";
    },


    methods: {
        removeTag: function removeTag(tag) {
            this.removeSelectedFilter(tag.id, tag.name);
        },
        resetAllTags: function resetAllTags() {
            this.removeAllAttribsAndRefresh();
        }
    }
});

},{"../../../mixins/url":24}],20:[function(require,module,exports){
"use strict";

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

var _url = require("../../../mixins/url");

var _url2 = _interopRequireDefault(_url);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

Vue.component("item-range-slider", {
    mixins: [_url2.default],

    props: ["template", "facet"],

    data: function data() {
        return {
            valueFrom: "",
            valueTo: ""
        };
    },
    created: function created() {
        var self = this;

        this.$options.template = this.template || "#vue-item-range-slider";

        var values = this.getSelectedFilterValue(this.facet.id);

        this.valueFrom = values ? values.min : this.facet.minValue;
        this.valueTo = values ? values.max : this.facet.maxValue;

        $(document).ready(function () {
            var element = self.$el.querySelector('#' + self.sanitizedFacetId);

            var slider = window.noUiSlider.create(element, {
                step: self.facet.step,
                start: [self.valueFrom, self.valueTo],
                connect: true,
                range: {
                    'min': Math.min(self.valueFrom, self.facet.minValue),
                    'max': Math.max(self.valueTo, self.facet.maxValue)
                }
            });

            slider.on('update', function (ui) {
                self.valueFrom = ui[0];
                self.valueTo = ui[1];
            });
        });
    },


    computed: _extends({
        sanitizedFacetId: function sanitizedFacetId() {
            return 'fl-range-slider-' + this.facet.id.replace(/\W/g, '-').replace(/-+/, '-').replace(/-$/, '');
        },
        isDisabled: function isDisabled() {
            return this.valueFrom === '' && this.valueTo === '' || parseFloat(this.valueFrom) > parseFloat(this.valueTo) || isNaN(this.valueFrom) || isNaN(this.valueTo) || this.valueFrom === '' || this.valueTo === '' || this.isLoading;
        }
    }, Vuex.mapState({
        isLoading: function isLoading(state) {
            return state.itemList.isLoading;
        }
    })),

    watch: {
        valueFrom: function valueFrom(value) {
            this.valueFrom = this.fixDecimalSeparator(value);
            this.setCustomValidationMessage();
        },
        valueTo: function valueTo(value) {
            this.valueTo = this.fixDecimalSeparator(value);
            this.setCustomValidationMessage();
        }
    },

    methods: {
        triggerFilter: function triggerFilter() {
            if (!this.isDisabled) {
                var facetValue = {
                    min: this.valueFrom,
                    max: this.valueTo ? this.valueTo : Number.MAX_SAFE_INTEGER
                };

                this.updateSelectedFilters(this.facet.id, facetValue);
            }
        },
        fixDecimalSeparator: function fixDecimalSeparator(value) {
            if (typeof value === 'number') {
                value = value.toString();
            }

            if (value.includes('.')) {
                value = value.replace(',', '');
            } else {
                value = value.replace(',', '.');
            }

            return value;
        },
        setCustomValidationMessage: function setCustomValidationMessage() {
            this.$el.querySelectorAll('input.fl-range-input[data-custom-validation-message]').forEach(function (input) {
                // Must be reset before the validity check as existence of custom validity counts as a validation error.
                input.setCustomValidity('');

                if (!input.checkValidity()) {
                    input.setCustomValidity(input.dataset.customValidationMessage);
                }
            });
        }
    }
});

},{"../../../mixins/url":24}],21:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
    value: true
});
var PARAMETER_ATTRIBUTES = 'attrib';
var PARAMETER_PAGE = 'page';
var PARAMETER_SORTING = 'sorting';
var PARAMETER_ITEMS = 'items';

exports.default = {
    PARAMETER_ATTRIBUTES: PARAMETER_ATTRIBUTES,
    PARAMETER_PAGE: PARAMETER_PAGE,
    PARAMETER_SORTING: PARAMETER_SORTING,
    PARAMETER_ITEMS: PARAMETER_ITEMS
};

},{}],22:[function(require,module,exports){
"use strict";

Vue.directive("render-category", {
    bind: function bind(el, binding) {
        el.onclick = function (event) {
            event.preventDefault();

            window.open(event.target.href, '_self');
        };
    }
});

},{}],23:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
    value: true
});

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

exports.default = {
    props: ["template", "facet"],

    data: function data() {
        return {
            isOpen: false
        };
    },

    created: function created() {
        this.$options.template = this.template || "#vue-item-dropdown";
    },


    computed: _extends({}, Vuex.mapState({
        isLoading: function isLoading(state) {
            return state.itemList.isLoading;
        }
    })),

    methods: {
        selected: function selected(value) {
            this.updateSelectedFilters(this.facet.id, value);
        },

        close: function close() {
            this.isOpen = false;
        },

        toggle: function toggle() {
            this.isOpen = !this.isOpen;
        }
    }
};

},{}],24:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
    value: true
});

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _constants = require("../constants");

var _constants2 = _interopRequireDefault(_constants);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

exports.default = {
    methods: {
        /*
         * Plentymarkets standard method for parsing params from string into object
         *
         * @param {string} urlParams
         * @returns {Object}
         */
        getUrlParams: function getUrlParams(urlParams) {
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
         * Findologic method for parsing url params into a parameter map from current url
         * Taken from direct integration flUtils class
         *
         * @returns {{}} The parameter map
         */
        getSearchParams: function getSearchParams() {
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

            var strArr = String(queryString).replace(/^&/, '').replace(/&$/, '').split('&'),
                sal = strArr.length,
                i,
                j,
                ct,
                p,
                lastObj,
                obj,
                lastIter,
                undef,
                chr,
                tmp,
                key,
                value,
                postLeftBracketPos,
                keys,
                keysLen,
                fixStr = function fixStr(queryString) {
                return decodeURIComponent(queryString.replace(/\+/g, '%20'));
            };

            for (i = 0; i < sal; i++) {
                tmp = strArr[i].split('=');
                key = fixStr(tmp[0]);
                value = tmp.length < 2 ? '' : fixStr(tmp[1]).replace(/\+/g, ' ');

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
                        key = keys[j].replace(/^['"]/, '').replace(/['"]$/, '');
                        lastIter = j !== keys.length - 1;
                        lastObj = obj;
                        if (key !== '' && key !== ' ' || j === 0) {
                            if (obj[key] === undef) {
                                obj[key] = {};
                            }
                            obj = obj[key];
                        } else {
                            // To insert new dimension
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

            if (requestParameters[_constants2.default.PARAMETER_ATTRIBUTES] === '') {
                delete requestParameters[_constants2.default.PARAMETER_ATTRIBUTES];
            }

            return requestParameters;
        },


        /*
         * Update url with selected filters
         *
         * @param {string} facetId
         * @param {string|array} facetValue
         */
        updateSelectedFilters: function updateSelectedFilters(facetId, facetValue) {
            var params = this.getSearchParams();

            if (!(_constants2.default.PARAMETER_ATTRIBUTES in params)) {
                params[_constants2.default.PARAMETER_ATTRIBUTES] = {};
            }

            var attributes = params[_constants2.default.PARAMETER_ATTRIBUTES];

            if (facetId === 'price' || this.facet.findologicFilterType === 'range-slider') {
                attributes[facetId] = {
                    min: facetValue.min,
                    max: facetValue.max
                };
            } else if (this.facet.select === 'single') {
                if (attributes[facetId] && Object.values(attributes[facetId]).includes(facetValue)) {
                    if (this.facet.id === 'cat' && facetValue.includes('_')) {
                        // Subcategory deselection
                        attributes[facetId] = [facetValue.split('_')[0]];
                    } else {
                        var index = Object.values(attributes[facetId]).indexOf(facetValue);
                        delete attributes[facetId][index];
                    }
                } else {
                    attributes[facetId] = [facetValue];
                }
            } else {
                if (!(facetId in attributes)) {
                    attributes[facetId] = [facetValue];
                } else {
                    var valueId = this.getKeyByValue(attributes[facetId], facetValue);

                    if (valueId === -1) {
                        var _index = Object.keys(attributes[facetId]).length;
                        attributes[facetId][_index] = facetValue;
                    } else {
                        delete attributes[facetId][valueId];
                    }
                }
            }

            params[_constants2.default.PARAMETER_ATTRIBUTES] = attributes;
            delete params[_constants2.default.PARAMETER_PAGE];

            document.location.search = '?' + $.param(params);
        },


        /*
         * Check if value is selected
         *
         * @param {string} facetId
         * @param {string} facetValue
         * @returns {boolean}
         */
        isValueSelected: function isValueSelected(facetId, facetValue) {
            var params = this.getSearchParams();

            if (!(_constants2.default.PARAMETER_ATTRIBUTES in params)) {
                return false;
            }

            var attributes = params[_constants2.default.PARAMETER_ATTRIBUTES];

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
        getSelectedFilters: function getSelectedFilters() {
            var selectedFilters = [];
            var params = this.getSearchParams();

            if (!(_constants2.default.PARAMETER_ATTRIBUTES in params)) {
                return selectedFilters;
            }

            var attributes = params[_constants2.default.PARAMETER_ATTRIBUTES];

            for (var filter in attributes) {
                if (filter === 'wizard') {
                    continue;
                }

                if (filter === 'price' || this.isRangeSliderFilter(attributes[filter])) {
                    var facetInfo = this.getFacetIdInfoMap();

                    var unit = facetInfo[filter] && facetInfo[filter].unit ? ' ' + facetInfo[filter].unit : '';

                    selectedFilters.push({
                        id: filter,
                        name: attributes[filter].min + unit + ' - ' + attributes[filter].max + unit
                    });

                    continue;
                }

                if (_typeof(attributes[filter]) === 'object') {
                    var values = attributes[filter];
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


        /**
         * @param attributeValue
         * @returns {boolean}
         */
        isRangeSliderFilter: function isRangeSliderFilter(attributeValue) {
            return typeof attributeValue.min !== 'undefined' && typeof attributeValue.max !== 'undefined';
        },


        /*
         * Remove selected filter from url
         *
         * @param {string} facetId
         * @param {string} facetValue
         */
        removeSelectedFilter: function removeSelectedFilter(facetId, facetValue) {
            facetValue = facetValue.replace(' > ', '_');
            var params = this.getSearchParams();
            var attributes = params[_constants2.default.PARAMETER_ATTRIBUTES];

            if (_typeof(attributes[facetId]) !== 'object' || facetId === 'price' || this.isRangeSliderFilter(attributes[facetId])) {
                delete attributes[facetId];
            } else {
                var values = attributes[facetId];
                for (var value in values) {
                    if (values[value] === facetValue) {
                        delete attributes[facetId][value];
                    }
                }
            }

            params[_constants2.default.PARAMETER_ATTRIBUTES] = attributes;
            delete params[_constants2.default.PARAMETER_PAGE];

            document.location.search = '?' + $.param(params);
        },


        /*
         * Get selected filter value from url
         *
         * @param {string} facetId
         * @returns {Object|null}
         */
        getSelectedFilterValue: function getSelectedFilterValue(facetId) {
            var params = this.getSearchParams();

            if (!(_constants2.default.PARAMETER_ATTRIBUTES in params)) {
                return null;
            }

            var attributes = params[_constants2.default.PARAMETER_ATTRIBUTES];

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
        getUrlParamValue: function getUrlParamValue(key) {
            var params = this.getSearchParams();

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
        setUrlParamValue: function setUrlParamValue(key, value) {
            var params = this.getSearchParams();

            params[key] = value;

            document.location.search = '?' + $.param(params);
        },


        /**
         * Set multiple url parameter values
         *
         * @param {array} keyValueArray
         */
        setUrlParamValues: function setUrlParamValues(keyValueArray) {
            var params = this.getSearchParams();

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
        getKeyByValue: function getKeyByValue(object, value) {
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
        getKeyBySuffix: function getKeyBySuffix(object, value) {
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
        removeAllAttribsAndRefresh: function removeAllAttribsAndRefresh() {
            var params = this.getSearchParams();
            delete params[_constants2.default.PARAMETER_PAGE];
            delete params[_constants2.default.PARAMETER_ATTRIBUTES];
            document.location.search = '?' + $.param(params);
        },
        getFacetIdInfoMap: function getFacetIdInfoMap() {
            var map = {};

            this.$store.state.itemList.facets.forEach(function (facet) {
                map[facet.id] = facet;
            });

            return map;
        }
    }
};

},{"../constants":21}]},{},[15,17,18,19,20,13,14,12,16,8,10,11,9,22])


//# sourceMappingURL=findologic-plugin.js.map
