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

namespace Mageplaza\SpecialPromotions\Api\Data;

/**
 * Interface CouponInterface
 * @package Mageplaza\SpecialPromotions\Api\Data
 */
interface CouponInterface
{
    const  COUPON_ID   = 'coupon_id';
    const  COUPON_CODE = 'coupon_code';

    /**
     * @return int
     */
    public function getCouponId();

    /**
     * @param int
     *
     * @return mixed
     */
    public function setCouponId($couponId);

    /**
     * @return string
     */
    public function getCouponCode();

    /**
     * @param string
     *
     * @return mixed
     */
    public function setCouponCode($couponCode);
}
