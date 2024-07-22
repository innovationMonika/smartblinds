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
use Mageplaza\SpecialPromotions\Api\Data\CouponDetailsInterface;

/**
 * Class CouponDetails
 * @package Mageplaza\SpecialPromotions\Model\Data
 */
class CouponDetails extends AbstractSimpleObject implements CouponDetailsInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRuleId()
    {
        return $this->_get(self::RULE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setRuleId($ruleId)
    {
        return $this->setData(self::RULE_ID, $ruleId);
    }

    /**
     * {@inheritdoc}
     */
    public function getRuleName()
    {
        return $this->_get(self::RULE_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setRuleName($ruleName)
    {
        return $this->setData(self::RULE_NAME, $ruleName);
    }

    /**
     * {@inheritdoc}
     */
    public function getRuleLabel()
    {
        return $this->_get(self::RULE_LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function setRuleLabel($ruleLabel)
    {
        return $this->setData(self::RULE_LABEL, $ruleLabel);
    }

    /**
     * {@inheritdoc}
     */
    public function getExpiredDate()
    {
        return $this->_get(self::EXPIRED_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setExpiredDate($expiredDate)
    {
        return $this->setData(self::EXPIRED_DATE, $expiredDate);
    }

    /**
     * {@inheritdoc}
     */
    public function getCoupons()
    {
        return $this->_get(self::COUPONS);
    }

    /**
     * {@inheritdoc}
     */
    public function setCoupons($coupons)
    {
        return $this->setData(self::COUPONS, $coupons);
    }

    /**
     * {@inheritdoc}
     */
    public function getUseAutoGeneration()
    {
        return $this->_get(self::USE_AUTO_GENERATION);
    }

    /**
     * {@inheritdoc}
     */
    public function setUseAutoGeneration($useAutoGeneration)
    {
        return $this->setData(self::USE_AUTO_GENERATION, $useAutoGeneration);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->_get(self::DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }
}
