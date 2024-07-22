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

namespace Mageplaza\SpecialPromotions\Model\Data;

use Magento\Framework\Api\AbstractSimpleObject;
use Mageplaza\SpecialPromotions\Api\Data\CouponInterface;

/**
 * Class Coupon
 * @package Mageplaza\SpecialPromotions\Model\Data
 */
class Coupon extends AbstractSimpleObject implements CouponInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCouponId()
    {
        return $this->_get(self::COUPON_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCouponId($couponId)
    {
        return $this->setData(self::COUPON_ID, $couponId);
    }

    /**
     * {@inheritdoc}
     */
    public function getCouponCode()
    {
        return $this->_get(self::COUPON_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setCouponCode($couponCode)
    {
        return $this->setData(self::COUPON_CODE, $couponCode);
    }
}
