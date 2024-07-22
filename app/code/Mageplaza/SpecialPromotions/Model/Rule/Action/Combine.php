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

namespace Mageplaza\SpecialPromotions\Model\Rule\Action;

use Exception;
use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\Condition\Context;
use Magento\SalesRule\Model\Rule\Condition\Product;
use Magento\SalesRule\Model\Rule\Condition\Product\Combine as SalesRuleProductCombine;
use Mageplaza\SpecialPromotions\Model\Config\Source\ItemAction;
use Mageplaza\SpecialPromotions\Model\Validator;

/**
 * Class Combine
 * @package Mageplaza\SpecialPromotions\Model\Rule\Action
 */
class Combine extends SalesRuleProductCombine
{
    /**
     * @var Validator
     */
    protected $validator;

    /**
     * Combine constructor.
     *
     * @param Context $context
     * @param Product $ruleConditionProduct
     * @param Validator $validator
     * @param array $data
     */
    public function __construct(
        Context $context,
        Product $ruleConditionProduct,
        Validator $validator,
        array $data = []
    ) {
        $this->validator = $validator;
        parent::__construct($context, $ruleConditionProduct, $data);
        $this->setMpProductXActions([]);
        $this->setMpProductYActions([]);
    }

    /**
     * @param AbstractModel $model
     *
     * @return bool
     */
    public function validate(AbstractModel $model)
    {
        if (parent::validate($model)) {
            return $this->validateAdditionalActions($model);
        }

        return false;
    }

    /**
     * @param AbstractModel $model
     *
     * @return bool
     */
    protected function validateAdditionalActions(AbstractModel $model)
    {
        $rule = $this->getRule();

        switch ($rule->getItemAction()) {
            case ItemAction::CHEAPEST_ACTION:
            case ItemAction::EXPENSIVE_ACTION:
                try {
                    $itemApplied = $this->validator->getRuleItemAppliedInfo($rule->getId());

                    return in_array($model->getId(), $itemApplied, false);
                } catch (Exception $e) {
                    return true;
                }
            case ItemAction::BUY_X_GET_Y_ACTION:
                break;
            case ItemAction::PRODUCT_SET_ACTION:
                break;
        }

        return true;
    }
}
