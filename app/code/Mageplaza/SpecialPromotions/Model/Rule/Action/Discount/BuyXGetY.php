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

namespace Mageplaza\SpecialPromotions\Model\Rule\Action\Discount;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\AbstractDiscount;
use Magento\SalesRule\Model\Rule\Action\Discount\DataFactory;
use Mageplaza\SpecialPromotions\Helper\Data;
use Mageplaza\SpecialPromotions\Model\RuleFactory as PromotionsRule;
use Mageplaza\SpecialPromotions\Model\Validator;

/**
 * Class BuyXGetY
 * @package Mageplaza\SpecialPromotions\Model\Rule\Action\Discount
 */
class BuyXGetY extends AbstractDiscount
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var PromotionsRule
     */
    protected $specialPromotionsRule;

    /**
     * BuyXGetY constructor.
     *
     * @param Validator $validator
     * @param DataFactory $discountDataFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param Data $helper
     * @param PromotionsRule $specialPromotionsRule
     */
    public function __construct(
        Validator $validator,
        DataFactory $discountDataFactory,
        PriceCurrencyInterface $priceCurrency,
        Data $helper,
        PromotionsRule $specialPromotionsRule
    ) {
        $this->helper = $helper;
        $this->specialPromotionsRule = $specialPromotionsRule;

        parent::__construct($validator, $discountDataFactory, $priceCurrency);
    }

    /**
     * @param Rule $rule
     * @param AbstractItem $item
     * @param float $qty
     *
     * @return Rule\Action\Discount\Data
     */
    public function calculate($rule, $item, $qty)
    {
        $specialPromotionsRule = $this->specialPromotionsRule->create()->load($rule->getId());

        /** @var Rule\Action\Discount\Data $discountData */
        $discountData = $this->discountFactory->create();
        $baseDiscount = 0;
        if ($this->helper->isEnabled($item->getStoreId()) && ($qtyDiscount = $this->fixQuantity($item->getQty(), $rule))
            && $specialPromotionsRule->getProductYActions()->validate($item)) {
            $baseDiscount = ($item->getBaseRowTotal() / $item->getQty()) * $qtyDiscount;
        }

        $discount = $this->priceCurrency->convert($baseDiscount, $item->getQuote()->getStore());

        $discountData->setAmount($discount);
        $discountData->setBaseAmount($baseDiscount);

        return $discountData;
    }

    /**
     * @param float $qty
     * @param Rule $rule
     *
     * @return float
     */
    public function fixQuantity($qty, $rule)
    {
        $maxXItemsQty = $this->validator->getMaxXItemsQty($rule->getId());
        $productYQty = (int)$rule->getMpProductYQty();
        $xItemsQty = $rule->getMpProductXQty();
        $yItemsQty = $rule->getMpProductYQty();

        if ($yItemsQty && $maxXItemsQty) {
            $xItemsStep = (int)($maxXItemsQty / $xItemsQty);
            $discountQty = $rule->getDiscountQty() ?: $xItemsStep;
            $discountStep = min($xItemsStep, $discountQty);

            return (min($productYQty * $discountStep, $qty));
        }

        return 0;
    }
}
