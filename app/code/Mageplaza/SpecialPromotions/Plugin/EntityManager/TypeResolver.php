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

namespace Mageplaza\SpecialPromotions\Plugin\EntityManager;

use Magento\SalesRule\Api\Data\RuleInterface;
use Mageplaza\SpecialPromotions\Model\Rule;
use Mageplaza\SpecialPromotions\Model\Rule\Interceptor;

/**
 * Class TypeResolver
 * @package Mageplaza\SpecialPromotions\Plugin\EntityManager
 */
class TypeResolver
{
    /**
     * @param \Magento\Framework\EntityManager\TypeResolver $subject
     * @param $result
     *
     * @return string
     */
    public function afterResolve(\Magento\Framework\EntityManager\TypeResolver $subject, $result)
    {
        if ($result === Interceptor::class ||
            $result === Rule::class) {
            return RuleInterface::class;
        }

        return $result;
    }
}
