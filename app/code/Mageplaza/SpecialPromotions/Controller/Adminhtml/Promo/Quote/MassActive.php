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

use Exception;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\SpecialPromotions\Controller\Adminhtml\Promo\Quote;

/**
 * Class MassActive
 * @package Mageplaza\SpecialPromotions\Controller\Adminhtml\Promo\Quote
 */
class MassActive extends Quote
{
    /**
     * @return Redirect|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $ruleIds = $this->getRequest()->getPost('rule_ids');
        if (!$ruleIds || !is_array($ruleIds)) {
            $this->messageManager->addErrorMessage(__('Rules does not exist.'));
        } else {
            $affectedRules = 0;
            $error = [];

            foreach ($ruleIds as $ruleId) {
                try {
                    $rule = $this->ruleFactory->create();
                    $this->ruleResource->load($rule, $ruleId);
                    $rule->setData('is_active', 1);
                    $this->ruleResource->save($rule);

                    $affectedRules++;
                } catch (Exception $e) {
                    $error[] = $ruleId;
                }
            }

            if ($affectedRules) {
                $this->getMessageManager()->addSuccessMessage(__('Active %1 rule(s).', $affectedRules));
            }

            if (count($error)) {
                $this->messageManager->addErrorMessage(__(
                    'An error occured while updating this rule(s): %1',
                    implode(', ', $error)
                ));
            }
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*');
    }
}
