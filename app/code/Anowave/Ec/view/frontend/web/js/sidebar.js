/**
 * Anowave Magento 2 Google Tag Manager Enhanced Ecommerce (UA) Tracking
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Anowave license that is
 * available through the world-wide-web at this URL:
 * https://www.anowave.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category 	Anowave
 * @package 	Anowave_Ec
 * @copyright 	Copyright (c) 2023 Anowave (https://www.anowave.com/)
 * @license  	https://www.anowave.com/license-agreement/
 */define(["jquery"],function(t){"use strict";return function(r){return t.widget("mage.sidebar",r,{_removeItemAfter:function(a,e){return e.hasOwnProperty("dataLayer")&&typeof dataLayer!="undefined"&&AEC.Cookie.remove(e.dataLayer).push(dataLayer),this._super(a)},_updateItemQtyAfter:function(a,e){return e.hasOwnProperty("dataLayer")&&typeof dataLayer!="undefined"&&AEC.Cookie.update(e.dataLayer).push(dataLayer),this._super(a)}}),t.mage.sidebar}});
