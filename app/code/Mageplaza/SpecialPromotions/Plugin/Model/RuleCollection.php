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

namespace Mageplaza\SpecialPromotions\Plugin\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Address;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection;
use Mageplaza\SpecialPromotions\Helper\Data;

/**
 * Class RuleCollection
 * @package Mageplaza\MultipleCoupons\Plugin\Model
 */
class RuleCollection
{
    /**
     * @var Data
     */
    private $helperData;

    /**
     * RuleCollection constructor.
     *
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param Collection $subject
     * @param $result
     * @param $websiteId
     * @param $customerGroupId
     * @param string $couponCode
     * @param null $now
     * @param Address|null $address
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function afterSetValidationFilter(
        Collection $subject,
        $result,
        $websiteId,
        $customerGroupId,
        $couponCode = '',
        $now = null,
        Address $address = null
    ) {
        if ($address && $address->getQuote() && $this->helperData->checkHasSpecialOrTierPrice($address->getQuote())) {
            $result->addFieldToFilter('mp_skip_special_tier_price', 0);
        }

        return $result;
    }
}
