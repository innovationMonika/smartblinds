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

namespace Mageplaza\SpecialPromotions\Plugin\Rule\Action\Discount;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\ByPercent;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;
use Magento\SalesRule\Model\Validator;
use Mageplaza\SpecialPromotions\Model\Config\Source\CalculateDiscount;

/**
 * Class ByPercentPlugin
 * @package Mageplaza\SpecialPromotions\Plugin\Rule\Action\Discount
 */
class ByPercentPlugin
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * ByPercentPlugin constructor.
     *
     * @param Validator $validator
     */
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param ByPercent $subject
     * @param Data $result
     * @param Rule $rule
     * @param AbstractItem $item
     * @param float $qty
     *
     * @return Data
     */
    public function afterCalculate(ByPercent $subject, $result, $rule, $item, $qty)
    {
        if ((int)$rule->getMpCalculateDiscount() === CalculateDiscount::USING_ORIGINAL_PRICE) {
            $itemOriginalPrice = $this->validator->getItemOriginalPrice($item);
            $baseItemOriginalPrice = $this->validator->getItemBaseOriginalPrice($item);
            $rulePercent = min(100, $rule->getDiscountAmount());
            $_rulePct = $rulePercent / 100;
            $result->setAmount(($qty * $itemOriginalPrice - $item->getDiscountAmount()) * $_rulePct);
            $result->setBaseAmount(($qty * $baseItemOriginalPrice - $item->getBaseDiscountAmount()) * $_rulePct);
        }

        return $result;
    }
}
