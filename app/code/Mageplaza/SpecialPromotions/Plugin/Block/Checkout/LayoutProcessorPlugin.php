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
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SpecialPromotions\Plugin\Block\Checkout;

use Mageplaza\MultipleCoupons\Block\Checkout\LayoutProcessor;
use Mageplaza\SpecialPromotions\Helper\Data;

/**
 * Class LayoutProcessorPlugin
 * @package Mageplaza\SpecialPromotions\Model\Plugin\Block
 */
class LayoutProcessorPlugin
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * LayoutProcessor constructor.
     *
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param LayoutProcessor $subject
     * @param array $jsLayout
     *
     * @return array
     */
    public function afterProcess(LayoutProcessor $subject, $jsLayout)
    {
        if ($this->helper->isModuleOutputEnabled('Mageplaza_MultipleCoupons')) {
            $discountComponent = &$jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['totals']['children']['discount'];
            if ($discountComponent['component'] === 'Mageplaza_MultipleCoupons/js/view/totals/discount') {
                $discountComponent['component'] = 'Mageplaza_SpecialPromotions/js/view/summary/promotions_multiple_coupons_discount';
            }
        }

        return $jsLayout;
    }
}
