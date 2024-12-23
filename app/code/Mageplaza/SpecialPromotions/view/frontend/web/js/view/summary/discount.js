/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_SpecialPromotions
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */define(["Magento_SalesRule/js/view/summary/discount","Magento_Checkout/js/model/totals"],function(e,i){"use strict";var n=window.checkoutConfig.enableDiscountDetails;return e.extend({defaults:{template:"Mageplaza_SpecialPromotions/summary/discount"},ifShowDetails:function(){return this.isFullMode()?this.getPureValue()!=0&&n&&this.getDetails().length>0:!1},getDetails:function(){var t=i.getSegment("discount");return t&&t.extension_attributes&&t.extension_attributes.discount_details?t.extension_attributes.discount_details:[]},formatPrice:function(t){return this.getFormattedPrice(t)}})});
