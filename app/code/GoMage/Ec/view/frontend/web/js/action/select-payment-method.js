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

define(['jquery','mage/utils/wrapper', 'Magento_Checkout/js/model/quote'], function($, wrapper, quote)
{
    'use strict';

    return function(paymentMethod)
    {
        return wrapper.wrap(paymentMethod, function (originalAction, method)
        {
        	if ('undefined' !== typeof dataLayer && 'undefined' !== typeof data)
        	{
	        	(function(dataLayer, $, paymentMethod)
	    		{
	        		if ("undefined" !== typeof fbq)
	        		{
	        			var content_ids = [], content_length = data.ecommerce.checkout.products.length;

	        			for (var i = 0, l = data.ecommerce.checkout.products.length; i < l; i++)
	        			{
	        				content_ids.push(data.ecommerce.checkout.products[i].id);
	        			}

	        			(function(callback)
	        			{
	        				if (AEC.Const.COOKIE_DIRECTIVE)
	        				{
	        					AEC.CookieConsent.queue(callback).process();
	        				}
	        				else
	        				{
	        					callback.apply(window,[]);
	        				}
	        			})
	        			(
	        				(function(info, content_ids, content_length)
	        				{
	        					return function()
	        					{
	        						fbq("track", "AddPaymentInfo",
	        						{
	        							value:			info.total,
	        							content_name: 	'checkout',
	        							content_ids:	content_ids,
	        							num_items:		content_length,
	        							currency: 		AEC.currencyCode,
	        							content_type:	(content_ids.length > 1) ? 'product group' : 'product'
	        						});
	        					}
	        				})(info,content_ids,content_length)
	        			);
	        		}

	    		})(dataLayer, jQuery, method);
        	}

            return originalAction(method);
        });
    };
});
