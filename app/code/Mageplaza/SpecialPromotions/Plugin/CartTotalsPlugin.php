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

namespace Mageplaza\SpecialPromotions\Plugin;

use DateTime;
use Exception;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\TotalsExtension;
use Magento\Quote\Api\Data\TotalsExtensionFactory;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Model\Quote;
use Magento\SalesRule\Model\Coupon as SalesRuleCoupon;
use Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory as CouponCollection;
use Magento\Store\Model\Store;
use Mageplaza\SpecialPromotions\Api\Data\CouponDetailsInterfaceFactory;
use Mageplaza\SpecialPromotions\Helper\Data;
use Mageplaza\SpecialPromotions\Model\Data\Coupon as CouponData;
use Mageplaza\SpecialPromotions\Model\Data\CouponFactory as CouponDataFactory;

/**
 * Class CartTotalsPlugin
 * @package Mageplaza\SpecialPromotions\Plugin
 */
class CartTotalsPlugin
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var TotalsExtensionFactory
     */
    private $totalsExtensionFactory;

    /**
     * @var CouponDetailsInterfaceFactory
     */
    private $couponDetailsFactory;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var CouponCollection
     */
    private $couponCollection;

    /**
     * @var CouponDataFactory
     */
    private $couponData;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * CartTotalsPlugin constructor.
     *
     * @param Data $helperData
     * @param TotalsExtensionFactory $totalsExtensionFactory
     * @param CouponDetailsInterfaceFactory $couponDetailsFactory
     * @param CartRepositoryInterface $cartRepository
     * @param CouponCollection $couponCollection
     * @param CouponDataFactory $couponData
     * @param TimezoneInterface $timezone
     * @param ResolverInterface $localeResolver
     */
    public function __construct(
        Data $helperData,
        TotalsExtensionFactory $totalsExtensionFactory,
        CouponDetailsInterfaceFactory $couponDetailsFactory,
        CartRepositoryInterface $cartRepository,
        CouponCollection $couponCollection,
        CouponDataFactory $couponData,
        TimezoneInterface $timezone,
        ResolverInterface $localeResolver
    ) {
        $this->helperData = $helperData;
        $this->totalsExtensionFactory = $totalsExtensionFactory;
        $this->couponDetailsFactory = $couponDetailsFactory;
        $this->cartRepository = $cartRepository;
        $this->couponCollection = $couponCollection;
        $this->couponData = $couponData;
        $this->timezone = $timezone;
        $this->localeResolver = $localeResolver;
    }

    /**
     * @param CartTotalRepositoryInterface $subject
     * @param TotalsInterface $result
     * @param int $cartId
     *
     * @return TotalsInterface
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function afterGet(CartTotalRepositoryInterface $subject, $result, $cartId)
    {
        /** @var Quote $quote */
        $quote = $this->cartRepository->getActive($cartId);
        $storeId = $quote->getStoreId();

        $extensionAttributes = $result->getExtensionAttributes();
        if (($extensionAttributes && $extensionAttributes->getMpCouponPopupDetails())
            || !$this->helperData->isEnabledCouponPopup($storeId)) {
            return $result;
        }

        /** @var TotalsExtension $productExtension */
        $totalsExtension = $extensionAttributes ?: $this->totalsExtensionFactory->create();

        $couponDetails = [];
        /** @var \Magento\SalesRule\Model\Rule $rule */
        foreach ($this->helperData->getRules($quote) as $rule) {
            $couponDetail = $this->couponDetailsFactory->create();
            $couponDetail->setRuleId($rule->getRuleId());
            $couponDetail->setRuleName($rule->getName());
            $couponDetail->setRuleLabel($rule->getStoreLabel($storeId));
            $couponDetail->setDescription($rule->getDescription());
            $expiredDate = $rule->getToDate()
                ? $this->getDateFormatted($rule->getToDate(), '2', $quote->getStore()) : null;
            $couponDetail->setExpiredDate($expiredDate);
            $useAutoGeneration = (int)$rule->getUseAutoGeneration();
            $couponDetail->setUseAutoGeneration($useAutoGeneration);
            $coupons = [];
            $couponItems = $this->couponCollection->create()
                ->addFieldToFilter('rule_id', $rule->getRuleId());

            if ($useAutoGeneration) {
                $couponItems->addFieldToFilter('type', SalesRuleCoupon::TYPE_GENERATED);
            } else {
                $couponItems->addFieldToFilter('type', SalesRuleCoupon::TYPE_MANUAL);
            }

            $couponItems->getItems();
            foreach ($couponItems->getItems() as $item) {
                $couponData = $this->setCouponData($item);
                $coupons[] = $couponData;
            }

            $couponDetail->setCoupons($coupons);
            $couponDetails[] = $couponDetail;
        }
        $totalsExtension->setMpCouponPopupDetails($couponDetails);
        $result->setExtensionAttributes($totalsExtension);

        return $result;
    }

    /**
     * @param DataObject|SalesRuleCoupon $coupon
     *
     * @return CouponData mixed
     */
    private function setCouponData($coupon)
    {
        $couponData = $this->couponData->create();
        $couponData->setCouponId((int)$coupon->getCouponId());
        $couponData->setCouponCode($coupon->getCode());

        return $couponData;
    }

    /**
     * @param string $date
     * @param int $format
     * @param Store $store
     *
     * @return string
     * @throws Exception
     */
    private function getDateFormatted($date, $format, $store)
    {
        return $this->timezone->formatDateTime(
            new DateTime($date),
            $format,
            $format,
            $this->localeResolver->getDefaultLocale(),
            $this->timezone->getConfigTimezone('store', $store),
            'yyyy-MM-dd'
        );
    }
}
