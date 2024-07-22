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

namespace Mageplaza\SpecialPromotions\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Framework\Validator\Exception;
use Magento\Quote\Model\Quote\Address;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\RulesApplier;
use Magento\SalesRule\Model\Utility;
use Magento\SalesRule\Model\Validator\Pool;
use Mageplaza\SpecialPromotions\Helper\Data;
use Mageplaza\SpecialPromotions\Model\Config\Source\ItemAction;
use Mageplaza\SpecialPromotions\Model\Config\Source\MaxDiscountType;
use Mageplaza\SpecialPromotions\Model\Config\Source\RuleType;
use Mageplaza\SpecialPromotions\Model\RuleFactory as SpecialPromotionsRuleFactory;

/**
 * Class Validator
 * @package Mageplaza\SpecialPromotions\Model
 */
class Validator extends \Magento\SalesRule\Model\Validator
{
    /**
     * The maximum discount of each rule
     *
     * @var array
     */
    protected $_ruleDiscountLimit = [];

    /**
     * List Item applied for additional action
     *
     * @var array
     */
    protected $_rulesItemApplied = [];

    /**
     * List Item applied for buy x get y action
     *
     * @var array
     */
    protected $buyXGetYItems = [];

    /**
     * The maximum discount for the whole cart
     *
     * @var float
     */
    protected $_cartDiscountLimit;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var SpecialPromotionsRuleFactory
     */
    private $specialPromotionsRule;

    /**
     * Validator constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param Utility $utility
     * @param RulesApplier $rulesApplier
     * @param PriceCurrencyInterface $priceCurrency
     * @param Pool $validators
     * @param ManagerInterface $messageManager
     * @param Data $helper
     * @param SpecialPromotionsRuleFactory $specialPromotionsRule
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CollectionFactory $collectionFactory,
        \Magento\Catalog\Helper\Data $catalogData,
        Utility $utility,
        RulesApplier $rulesApplier,
        PriceCurrencyInterface $priceCurrency,
        Pool $validators,
        ManagerInterface $messageManager,
        Data $helper,
        SpecialPromotionsRuleFactory $specialPromotionsRule,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->specialPromotionsRule = $specialPromotionsRule;

        parent::__construct(
            $context,
            $registry,
            $collectionFactory,
            $catalogData,
            $utility,
            $rulesApplier,
            $priceCurrency,
            $validators,
            $messageManager,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @param mixed $items
     * @param Address $address
     *
     * @return $this
     * @throws Exception
     */
    public function initActionTotals($items, Address $address)
    {
        $this->_rulesItemTotals = [];
        if (!$items) {
            return $this;
        }

        $this->_cartDiscountLimit = $this->calculateMaxDiscountForRule($address);

        /** @var Rule $rule */
        foreach ($this->_getRules($address) as $rule) {
            if (!$this->validatorUtility->canProcessRule($rule, $address)) {
                continue;
            }

            $this->_ruleDiscountLimit[$rule->getId()] = $this->calculateMaxDiscountForRule($address, $rule);

            switch ($rule->getItemAction()) {
                case ItemAction::CHEAPEST_ACTION:
                    $isCheapest = true;
                    // no break
                case ItemAction::EXPENSIVE_ACTION:
                    $itemApplied = [];
                    foreach ($items as $item) {
                        //Skipping child items to avoid double calculations
                        if ($item->getParentItemId()) {
                            continue;
                        }
                        if (!$rule->getActions()->validate($item)) {
                            continue;
                        }
                        if (!$this->canApplyDiscount($item)) {
                            continue;
                        }

                        $itemApplied[$item->getId()] = $this->getItemBasePrice($item);
                    }

                    $qtyApplied = (int)$rule->getItemActionQty() ?: 1;

                    isset($isCheapest) ? asort($itemApplied) : arsort($itemApplied);
                    $this->_rulesItemApplied[$rule->getId()] = [];
                    foreach ($itemApplied as $key => $price) {
                        $this->_rulesItemApplied[$rule->getId()][] = $key;
                        if (--$qtyApplied === 0) {
                            break;
                        }
                    }
                    break;
                //                case ItemAction::BUY_X_GET_Y_ACTION:
                //                    break;
                //                case ItemAction::PRODUCT_SET_ACTION:
                //                    break;
            }
        }

        /** Init Cart spent Rule */
        /** @var Rule $rule */
        foreach ($this->_getRules($address) as $rule) {
            if (RuleType::BUY_X_ITEM_GET_Y_ITEM === $rule->getSimpleAction()
                && $this->validatorUtility->canProcessRule($rule, $address)) {
                foreach ($items as $item) {
                    if ($item->getParentItemId()) {
                        continue;
                    }
                    if (!$rule->getActions()->validate($item)) {
                        continue;
                    }
                    if (!$this->canApplyDiscount($item)) {
                        continue;
                    }
                    $this->setMaxXItemsQty($rule, $item);
                }
            }
            if (RuleType::CART_SPENT_X_GET_Y_ACTION == $rule->getSimpleAction()
                && $this->validatorUtility->canProcessRule($rule, $address)
            ) {
                $ruleTotalItemsPrice = 0;
                $ruleTotalBaseItemsPrice = 0;
                $validItemsCount = 0;

                foreach ($items as $item) {
                    //Skipping child items to avoid double calculations
                    if ($item->getParentItemId()) {
                        continue;
                    }
                    if (!$rule->getActions()->validate($item)) {
                        continue;
                    }
                    if (!$this->canApplyDiscount($item)) {
                        continue;
                    }

                    $qty = $this->validatorUtility->getItemQty($item, $rule);
                    $ruleTotalItemsPrice += $this->getItemPrice($item) * $qty;
                    $ruleTotalBaseItemsPrice += $this->getItemBasePrice($item) * $qty;
                    $validItemsCount++;
                }

                $baseDiscountAmount = 0;

                $discountStep = $rule->getDiscountStep();
                $discountAmount = $rule->getDiscountAmount();
                if ($discountStep && $discountAmount && $discountStep > $discountAmount) {
                    $fullRulePeriod = floor($ruleTotalBaseItemsPrice / $discountStep);

                    $baseDiscountAmount = $fullRulePeriod * $discountAmount;
                }

                $this->_rulesItemTotals[$rule->getId()] = [
                    'items_price' => $ruleTotalItemsPrice,
                    'base_items_price' => $ruleTotalBaseItemsPrice,
                    'base_discount_amount' => $baseDiscountAmount,
                    'items_count' => $validItemsCount,
                ];
            }
        }

        return $this;
    }

    /**
     * @param Rule $rule
     * @param mixed $item
     */
    private function setMaxXItemsQty($rule, $item)
    {
        $specialPromotionsRule = $this->specialPromotionsRule->create()->load($rule->getId());
        $qty = $item->getQty();
        $productXQty = (int)$rule->getMpProductXQty();
        if ($productXQty && $qty >= $productXQty && $specialPromotionsRule->getProductXActions()->validate($item)) {
            $maxXItemsQty = 0;
            $this->buyXGetYItems[$rule->getId()] = max($maxXItemsQty, $qty);
        }
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getMaxXItemsQty($key)
    {
        if (empty($this->buyXGetYItems[$key])) {
            return 0;
        }

        return $this->buyXGetYItems[$key];
    }

    /**
     * @param $key
     *
     * @return float|mixed|null
     */
    public function getRuleMaxDiscountInfo($key)
    {
        if ($this->_cartDiscountLimit === null && $this->_ruleDiscountLimit[$key] === null) {
            return null;
        }

        if ($this->_cartDiscountLimit === null) {
            return $this->_ruleDiscountLimit[$key];
        }

        if ($this->_ruleDiscountLimit[$key] === null) {
            return $this->_cartDiscountLimit;
        }

        return min($this->_ruleDiscountLimit[$key], $this->_cartDiscountLimit);
    }

    /**
     * @param $key
     * @param $amount
     *
     * @return $this
     */
    public function decrementRuleMaxDiscount($key, $amount)
    {
        if ($this->_ruleDiscountLimit[$key]) {
            $this->_ruleDiscountLimit[$key] -= $amount;
        }

        if ($this->_cartDiscountLimit) {
            $this->_cartDiscountLimit -= $amount;
        }

        return $this;
    }

    /**
     * @param Address $address
     * @param Rule | null $rule
     *
     * @return float|int|null
     */
    protected function calculateMaxDiscountForRule($address, $rule = null)
    {
        if ($rule === null) {
            $maxDiscountType = (int)$this->helper->getConfigGeneral('maximum_discount_type');
            $maxDiscount = (float)$this->helper->getConfigGeneral('maximum_discount');
        } else {
            $maxDiscountType = (int)$rule->getMaximumDiscountType();
            $maxDiscount = (float)$rule->getMaximumDiscount();
        }

        if ($maxDiscountType !== MaxDiscountType::TYPE_NO && $maxDiscount > 0) {
            if ($maxDiscountType === MaxDiscountType::TYPE_PERCENT) {
                $maxDiscount *= $address->getBaseSubtotal() / 100;
            }

            return abs($maxDiscount);
        }

        return null;
    }

    /**
     * @param int $key
     *
     * @return array
     * @throws LocalizedException
     */
    public function getRuleItemAppliedInfo($key)
    {
        if (empty($this->_rulesItemApplied[$key])) {
            throw new LocalizedException(__('Applied item are not set for the rule.'));
        }

        return $this->_rulesItemApplied[$key];
    }
}
