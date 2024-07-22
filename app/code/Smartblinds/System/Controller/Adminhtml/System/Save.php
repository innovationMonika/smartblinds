<?php

namespace Smartblinds\System\Controller\Adminhtml\System;

use Exception;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;

class Save extends Base
{
    public function execute()
    {
        $isRedirectBack = $this->getRequest()->getParam('back', false);
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $system = $this->initSystem();

        $data = $this->getRequest()->getPostValue();
        $data['storeviews'] = implode(",", $data['store_id']);

        if (!is_array($data)) {
            return $resultRedirect->setPath('*/*');
        }

        if (!$data['id']) {
            unset($data['id']);
        }

        if((float)$data['min_width'] > (float)$data['max_width']){
            $isRedirectBack = true;
            $this->messageManager->addErrorMessage(__("Max Width must be greater than Min Width."));
        }

        if((float)$data['min_height'] > (float)$data['max_height']){
            $isRedirectBack = true;
            $this->messageManager->addErrorMessage(__("Max Height must be greater than Min Height."));
        }

        $system->addData($data);

        try {
            $this->systemResource->save($system);
            $this->messageManager->addSuccessMessage(__('You saved the system.'));
        } catch (Exception $e) {
            $isRedirectBack = true;
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }

        return ($isRedirectBack)
            ? $resultRedirect->setPath('*/*/edit', ['id' => $system->getId()])
            : $resultRedirect->setPath('*/*');
    }
}
