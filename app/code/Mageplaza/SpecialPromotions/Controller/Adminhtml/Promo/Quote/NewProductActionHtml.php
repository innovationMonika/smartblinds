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

namespace Mageplaza\SpecialPromotions\Controller\Adminhtml\Promo\Quote;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\SalesRule\Controller\Adminhtml\Promo\Quote;
use Mageplaza\SpecialPromotions\Model\Rule;

/**
 * Class NewProductActionHtml
 * @package Mageplaza\SpecialPromotions\Controller\Adminhtml\Promo\Quote
 */
class NewProductActionHtml extends Quote implements HttpPostActionInterface
{
    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        // TODO: Implement execute() method.
    }

    /**
     * @param $prefix
     * @param $ruleAction
     */
    public function setBodyResponse($prefix, $ruleAction)
    {
        $id = $this->getRequest()
            ->getParam('id');
        $formName = $this->getRequest()
            ->getParam('form_namespace');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = $this->_objectManager->create(
            $type
        )->setId(
            $id
        )->setType(
            $type
        )->setRule(
            $this->_objectManager->create(Rule::class)
        )->setPrefix(
            $prefix
        );
        if (!$model->getConditions()) {
            $model->setConditions([]);
        }
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof AbstractCondition) {
            $model->setJsFormObject($formName);
            $model->setFormName($formName);
            $this->setJsFormObject($model, $ruleAction);
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()
            ->setBody($html);
    }

    /**
     * Set jsFormObject for the model object
     *
     * @param AbstractCondition $model
     * @param string $ruleAction
     *
     * @return void
     */
    private function setJsFormObject(AbstractCondition $model, $ruleAction)
    {
        $requestJsFormName = $this->getRequest()->getParam('form');
        $actualJsFormName = $this->getJsFormObjectName($model->getFormName(), $ruleAction);
        if ($requestJsFormName === $actualJsFormName) { //new
            $model->setJsFormObject($actualJsFormName);
        } else { //edit
            $model->setJsFormObject($requestJsFormName);
        }
    }

    /**
     * Get jsFormObject name
     *
     * @param string $formName
     * @param string $ruleAction
     *
     * @return string
     */
    private function getJsFormObjectName($formName, $ruleAction)
    {
        return $formName . $ruleAction;
    }
}
