<?php
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
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SpecialPromotions\Controller\Adminhtml\Promo\Quote;

/**
 * Class NewProductXActionHtml
 * @package Mageplaza\SpecialPromotions\Controller\Adminhtml\Promo\Quote
 */
class NewProductXActionHtml extends NewProductActionHtml
{
    /**
     * New action html action
     *
     * @return void
     */
    public function execute()
    {
        $this->setBodyResponse('mp_product_x_actions', 'product_x_rule_actions_fieldset_');
    }
}
