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

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class SkipType
 * @package Mageplaza\SpecialPromotions\Model\Config\Source
 */
class SkipType implements OptionSourceInterface
{
    const SPECIAL_PRICE = 'special_price';
    const TIER_PRICE = 'tier_price';
    const BUNDLE_WHEN_CHILD_HAS_SPECIAL_PRICE = 'bundle_when_child_has_special_price';

    /**
     * Return websites array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('Special Price'), 'value' => self::SPECIAL_PRICE],
            ['label' => __('Tier Price'), 'value' => self::TIER_PRICE],
            [
                'label' => __('Bundle Items when Child has Special Price'),
                'value' => self::BUNDLE_WHEN_CHILD_HAS_SPECIAL_PRICE
            ],
        ];
    }
}
