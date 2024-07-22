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

namespace Mageplaza\SpecialPromotions\Model\Config\Source;

use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\SimpleActionOptionsProvider;

/**
 * Class RuleType
 * @package Mageplaza\SpecialPromotions\Model\Config\Source
 */
class RuleType extends SimpleActionOptionsProvider
{
    const SPENT_X_GET_Y_ACTION = 'spent_x_get_y';
    const CART_SPENT_X_GET_Y_ACTION = 'cart_spent_x_get_y';
    const BUY_X_ITEM_GET_Y_ITEM = 'buy_x_item_get_y_item';

    /**
     * Return websites array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array_merge(parent::toOptionArray(), $this->specialRules());
    }

    /**
     * @return array[]
     */
    public function specialRules()
    {
        return [
            ['label' => __('To-fixed amount discount'), 'value' => Rule::TO_FIXED_ACTION],
            ['label' => __('For each $X spent, get $Y discount'), 'value' => self::SPENT_X_GET_Y_ACTION],
            [
                'label' => __('For each $X spent, get $Y discount for the whole cart'),
                'value' => self::CART_SPENT_X_GET_Y_ACTION
            ],
            ['label' => __('Buy X get Y'), 'value' => self::BUY_X_ITEM_GET_Y_ITEM],
        ];
    }
}
