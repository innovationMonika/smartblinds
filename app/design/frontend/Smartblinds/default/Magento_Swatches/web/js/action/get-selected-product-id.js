define(["jquery","underscore"],function(r,t){"use strict";var e={swatchOptions:'div[data-role="swatch-options"]',swatchInput:".swatch-input"};return function(){var c=r(e.swatchOptions),f=c.find(e.swatchInput),d=window.jsonConfig,i=[],n=!0;if(f.each(function(){var s=r(this),u=s.data("attr-name");if(!u){n=!1;return}var h=t.findWhere(d.attributes,{code:u.toString()}),o=t.findWhere(h.options,{id:s.val().toString()});if(!o){n=!1;return}i.push(o.products)}),!n)return null;var a=t.intersection.apply(t,i);return a.length===1?a[0]:null}});
