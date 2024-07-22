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
 * Class CalculateDiscount
 * @package Mageplaza\SpecialPromotions\Model\Config\Source
 */
class CalculateDiscount implements OptionSourceInterface
{
    const USING_SPECIAL_TIER_PRICE = 0;
    const USING_ORIGINAL_PRICE = 1;

    /**
     * Return websites array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('Using Special Price/Tier Price'), 'value' => self::USING_SPECIAL_TIER_PRICE],
            ['label' => __('Using Original Price'), 'value' => self::USING_ORIGINAL_PRICE]
        ];
    }
}
