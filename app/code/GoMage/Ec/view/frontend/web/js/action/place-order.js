/**
 * Anowave Magento 2 Google Tag Manager Enhanced Ecommerce (UA) Tracking
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Anowave license that is
 * available through the world-wide-web at this URL:
 * http://www.anowave.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category 	Anowave
 * @package 	Anowave_Ec
 * @copyright 	Copyright (c) 2021 Anowave (http://www.anowave.com/)
 * @license  	http://www.anowave.com/license-agreement/
 */

define(['jquery','mage/utils/wrapper'], function ($, wrapper)
{
    'use strict';

    return function (placeOrderAction)
    {
        return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, messageContainer)
        {
            if (typeof AEC === 'undefined' || typeof AEC.Const === 'undefined') {
                return originalAction(paymentData, messageContainer);
            }
            if ('undefined' !== typeof AEC.Const.CHECKOUT_STEP_PAYMENT)
            {
                var method = paymentData.method;
                AEC.Checkout.stepOption(AEC.Const.CHECKOUT_STEP_PAYMENT, method);
            }

        	if ('undefined' !== typeof data && 'undefined' !== typeof AEC.Const.CHECKOUT_STEP_ORDER)
        	{
        		/**
        		 * Set step
        		 */
        		data.ecommerce.checkout.actionField.step = AEC.Const.CHECKOUT_STEP_ORDER;

        		/**
        		 * Push checkout step
        		 */
        		AEC.Cookie.checkout(data).push(dataLayer);

        		/**
        		 * Push a place order event
        		 */
        		dataLayer.push({ event: 'placeOrder' });
        	}

            return originalAction(paymentData, messageContainer);
        });
    };
});
