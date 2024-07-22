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
 * Interface CouponDetailsInterface
 * @package Mageplaza\SpecialPromotions\Api\Data
 */
interface CouponDetailsInterface
{
    const  RULE_ID             = 'rule_id';
    const  RULE_NAME           = 'rule_name';
    const  RULE_LABEL          = 'rule_label';
    const  COUPONS             = 'coupons';
    const  EXPIRED_DATE        = 'expired_date';
    const  USE_AUTO_GENERATION = 'use_auto_generation';
    const  DESCRIPTION         = 'description';

    /**
     * @return int
     */
    public function getRuleId();

    /**
     * @param int
     *
     * @return mixed
     */
    public function setRuleId($ruleId);

    /**
     * @return string
     */
    public function getRuleName();

    /**
     * @param string
     *
     * @return mixed
     */
    public function setRuleName($ruleName);

    /**
     * @return string
     */
    public function getRuleLabel();

    /**
     * @param string
     *
     * @return mixed
     */
    public function setRuleLabel($ruleLabel);

    /**
     * @return string
     */
    public function getExpiredDate();

    /**
     * @param string
     *
     * @return mixed
     */
    public function setExpiredDate($expiredDate);

    /**
     * @return \Mageplaza\SpecialPromotions\Api\Data\CouponInterface[]
     */
    public function getCoupons();

    /**
     * @param \Mageplaza\SpecialPromotions\Api\Data\CouponInterface[]
     *
     * @return mixed
     */
    public function setCoupons($coupons);

    /**
     * @return int
     */
    public function getUseAutoGeneration();

    /**
     * @param int
     *
     * @return mixed
     */
    public function setUseAutoGeneration($useAutoGeneration);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string
     *
     * @return mixed
     */
    public function setDescription($description);
}
