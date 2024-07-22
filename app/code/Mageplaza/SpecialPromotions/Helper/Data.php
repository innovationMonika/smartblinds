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

namespace Mageplaza\SpecialPromotions\Helper;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\SalesRule\Model\Quote\ChildrenValidationLocator;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory as SalesRuleCollection;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Utility;
use Magento\SalesRule\Model\Validator;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\SpecialPromotions\Model\Config\Source\SkipType;

/**
 * Class Data
 * @package Mageplaza\SpecialPromotions\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'specialpromotions';
    const CONFIG_SPECIAL_TIER = 'skip_special_tier';
    /**
     * @var SalesRuleCollection
     */
    private $ruleCollectionFactory;
    /**
     * @var Utility
     */
    private $validatorUtility;
    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var array
     */
    private $rules = [];

    /**
     * @var ChildrenValidationLocator
     */
    private $childrenValidationLocator;
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param SalesRuleCollection $ruleCollectionFactory
     * @param Utility $validatorUtility
     * @param Validator $validator
     * @param ChildrenValidationLocator $childrenValidationLocator
     * @param ProductRepository $productRepository
     * @param TimezoneInterface $localeDate
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        SalesRuleCollection $ruleCollectionFactory,
        Utility $validatorUtility,
        Validator $validator,
        ChildrenValidationLocator $childrenValidationLocator,
        ProductRepository $productRepository,
        TimezoneInterface $localeDate
    ) {
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->validatorUtility = $validatorUtility;
        $this->validator = $validator;
        $this->childrenValidationLocator = $childrenValidationLocator;
        $this->productRepository = $productRepository;
        $this->localeDate = $localeDate;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    public function isDebugEnabled($storeId = null)
    {
        if (!$this->isEnabled($storeId) || !$this->getModuleConfig('develop/enabled')) {
            return false;
        }

        $whiteList = $this->getModuleConfig('develop/whitelist_ip');
        if (!$whiteList) {
            return true;
        }

        $clientIps = array_filter(array_map('trim', explode(',', $this->_request->getClientIp())));
        $clientIp = end($clientIps);
        $whiteList = explode(',', $whiteList);
        foreach ($whiteList as $ip) {
            if ($this->checkIp($clientIp, $ip)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check Ip
     *
     * @param $ip
     * @param $range
     *
     * @return bool
     */
    public function checkIp($ip, $range)
    {
        if (strpos($range, '*') !== false) {
            $low = $high = $range;
            if (strpos($range, '-') !== false) {
                [$low, $high] = explode('-', $range, 2);
            }
            $low = str_replace('*', '0', $low);
            $high = str_replace('*', '255', $high);
            $range = $low . '-' . $high;
        }
        if (strpos($range, '-') !== false) {
            [$low, $high] = explode('-', $range, 2);

            return $this->ipCompare($ip, $low, 1) && $this->ipcompare($ip, $high, -1);
        }

        return $this->ipCompare($ip, $range);
    }

    /**
     * @param $ip1
     * @param $ip2
     * @param int $op
     *
     * @return bool
     */
    private function ipCompare($ip1, $ip2, $op = 0)
    {
        $ip1Arr = explode('.', $ip1);
        $ip2Arr = explode('.', $ip2);

        for ($i = 0; $i < 4; $i++) {
            if ($ip1Arr[$i] < $ip2Arr[$i]) {
                return ($op === -1);
            }
            if ($ip1Arr[$i] > $ip2Arr[$i]) {
                return ($op === 1);
            }
        }

        return ($op === 0);
    }

    /********************************** Special/Tier Price Configuration *********************
     *
     * @param string $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getSpecialTierConfig($code = '', $storeId = null)
    {
        $code = $code ? self::CONFIG_SPECIAL_TIER . '/' . $code : self::CONFIG_SPECIAL_TIER;

        return $this->getModuleConfig($code, $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function isEnabledSpecialTier($storeId = null)
    {
        return $this->getSpecialTierConfig('enabled', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getNotApplySpecialTier($storeId = null)
    {
        return $this->getSpecialTierConfig('not_apply', $storeId);
    }

    /**
     * @param Quote $quote
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function checkHasSpecialOrTierPrice($quote)
    {
        $storeId = $quote->getStoreId();
        if ($this->isEnabledSpecialTier($storeId)) {
            foreach ($quote->getAllItems() as $item) {
                $applyConfig = explode(',', $this->getNotApplySpecialTier($storeId));
                if ($item->getProductType() !== Type::TYPE_BUNDLE) {
                    try {
                        $product = $this->productRepository->get($item->getProduct()->getSku());
                        if ($product && (($this->isSpecialPrice($product)
                                    && in_array(SkipType::SPECIAL_PRICE, $applyConfig, true))
                                || ($product->getTierPrice($item->getQty()) < $product->getPrice()
                                    && in_array(SkipType::TIER_PRICE, $applyConfig, true)))
                        ) {
                            return true;
                        }
                    } catch (NoSuchEntityException $e) {
                        $this->_logger->critical($e->getMessage());
                    }
                }

                if ($item->getProductType() === Type::TYPE_BUNDLE
                    && in_array(SkipType::BUNDLE_WHEN_CHILD_HAS_SPECIAL_PRICE, $applyConfig, true)) {
                    foreach ($item->getChildren() as $childItem) {
                        if ($this->isSpecialPrice($childItem->getProduct())) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param Product $product
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isSpecialPrice($product)
    {
        $store = $this->storeManager->getStore();
        $price = (float)$product->getPrice();
        $specialPrice = $product->getSpecialPrice();
        if ($specialPrice !== null
            && $specialPrice !== false
            && $this->localeDate->isScopeDateInInterval(
                $store,
                $product->getSpecialFromDate(),
                $product->getSpecialToDate()
            )
        ) {
            return $price > $specialPrice;
        }

        return false;
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function isEnabledCouponPopup($storeId = null)
    {
        return $this->getModuleConfig('coupon_popup/enabled', $storeId) && $this->isEnabled($storeId);
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    public function isDisabledCouponPopup($storeId = null)
    {
        return !$this->isEnabledCouponPopup($storeId);
    }

    /**
     * @param Quote $quote
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getRules($quote)
    {
        $websiteId = $this->storeManager->getStore($quote->getStoreId())->getWebsiteId();
        $customerGroupId = $quote->getCustomerGroupId();
        $rulesCollection = $this->ruleCollectionFactory->create()
            ->addWebsiteGroupDateFilter($websiteId, $customerGroupId)
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('coupon_type', Rule::COUPON_TYPE_SPECIFIC)
            ->addFieldToFilter('mp_enable_coupon_pickup', 1);
        if ($this->checkHasSpecialOrTierPrice($quote)) {
            $rulesCollection->addFieldToFilter('mp_skip_special_tier_price', 0);
        }

        $rules = $rulesCollection->load()->getItems();
        /** @var Item $item */
        foreach ($quote->getAllItems() as $item) {
            if ($item->getNoDiscount() || !$this->validator->canApplyDiscount($item) || $item->getParentItem()) {
                continue;
            }

            $address = $item->getAddress();
            /** @var Rule $rule */
            foreach ($rules as $rule) {
                if (!$this->validatorUtility->canProcessRule($rule, $address)) {
                    continue;
                }

                if (!$rule->getActions()->validate($item)) {
                    if (!$this->childrenValidationLocator->isChildrenValidationRequired($item)) {
                        continue;
                    }
                    $childItems = $item->getChildren();
                    $isContinue = true;
                    if (!empty($childItems)) {
                        foreach ($childItems as $childItem) {
                            if ($rule->getActions()->validate($childItem)) {
                                $isContinue = false;
                            }
                        }
                    }
                    if ($isContinue) {
                        continue;
                    }
                }

                if (!isset($this->rules[$rule->getId()])) {
                    $this->rules[$rule->getId()] = $rule;
                }
            }
        }

        return $this->rules;
    }

    /**
     * @return string
     */
    public function getCouponMessageComponent()
    {
        return $this->isEnabledMultipleCoupons()
            ? 'Mageplaza_MultipleCoupons/js/view/messages'
            : 'Magento_SalesRule/js/view/payment/discount-messages';
    }

    /**
     * @return string
     */
    public function getCouponPopupComponent()
    {
        return $this->isEnabledMultipleCoupons()
            ? 'Mageplaza_SpecialPromotions/js/view/coupon-popup-compatible-multiple-coupons'
            : 'Mageplaza_SpecialPromotions/js/view/coupon-popup';
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isEnabledMultipleCoupons($storeId = null)
    {
        $isMultipleCoupons = explode(',', $this->getConfigValue('mpmultiplecoupons/general/apply_page', $storeId) ?? '');

        return $this->isModuleOutputEnabled('Mageplaza_MultipleCoupons')
            && $this->getConfigValue('mpmultiplecoupons/general/enabled', $storeId)
            && in_array('frontend', $isMultipleCoupons, true);
    }
}
