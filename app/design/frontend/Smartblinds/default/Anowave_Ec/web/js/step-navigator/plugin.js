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

define(function ()
{
    'use strict';

    return function (target)
    {
    	var steps = target.steps;

    	target.next = function()
    	{
            var activeIndex = 0,
                code;

            steps().sort(this.sortItems).forEach(function (element, index) {
                if (element.isVisible()) {
                    element.isVisible(false);
                    activeIndex = index;
                }
            });

            if (steps().length > activeIndex + 1) {
                code = steps()[activeIndex + 1].code;
                if (typeof AEC !== 'undefined' && 'undefined' !== typeof AEC.Checkout.step) {
                    AEC.Checkout.step(activeIndex, activeIndex + 1, code);
                }
                steps()[activeIndex + 1].isVisible(true);
                this.setHash(code);
                document.body.scrollTop = document.documentElement.scrollTop = 0;
            }
        }

    	return target;
    };
});
