define(["jquery","mage/utils/wrapper","Magento_Checkout/js/model/quote"],function(o,u,i){"use strict";return function(n){return u.wrap(n,function(r){var t=i.shippingAddress();return t.extension_attributes===void 0&&(t.extension_attributes={}),t.customAttributes!==void 0&&t.customAttributes.forEach(function(e,s,a){t.extension_attributes[e.attribute_code]=e.value}),r()})}});
