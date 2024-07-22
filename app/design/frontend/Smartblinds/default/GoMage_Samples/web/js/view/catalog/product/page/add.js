define(["jquery","underscore","sampleBasket","getSwatchSelectedProductId"],function(n,l,o,w){"use strict";var I=n(window),t={selectors:{swatchOptions:'div[data-role="swatch-options"]',swatchInput:".swatch-input",sampleClick:".js-sample-click",sampleState:".js-sample-state"}},d=null,a,c,h,r;function v(){if(I.on("sample-basket-updated",p),a=n(t.selectors.swatchOptions),!!a.length){if(c=a.find(t.selectors.swatchInput),!c.length){a.on("swatch.initialized",u);return}u()}}function u(){c=a.find(t.selectors.swatchInput),c.on("change",m),h=n(t.selectors.sampleClick),h.on("click",k),r=n(t.selectors.sampleState),m()}function m(e){var s=parseInt(w());s&&(d=s,p())}function k(){var e=l.findWhere(t.options.items,{id:d});if(e){if(o.getItemBySwatches(e)){o.removeItemBySwatches(e);return}try{var s=null;e.parentId&&(s=l.findWhere(t.options.items,{id:e.parentId})),o.addItem(e,s)}catch(i){alert(i)}p()}}function p(e){var s=l.findWhere(t.options.items,{id:d});if(s&&o.getItemBySwatches(s)){r.html('<span class="tick-icon">'+t.options.addedToCartIcon+"</span>"),r.parents(t.selectors.sampleClick).addClass("active");return}r.each(function(){var i=n(this),f="";i.data("hide-add-button")||(f=t.options.addToCartIcon),i.html(f),i.parents(t.selectors.sampleClick).removeClass("active")})}return function(e){t.options=e,v()}});
