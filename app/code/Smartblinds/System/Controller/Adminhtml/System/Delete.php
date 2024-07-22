<?php declare(strict_types=1);

namespace Smartblinds\System\Controller\Adminhtml\System;

use Magento\Backend\Model\View\Result\Redirect;

class Delete extends Base
{
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $rule = $this->initSystem();
                $this->systemResource->delete($rule);
                $this->messageManager->addSuccessMessage(__('You deleted the system.'));
                return $resultRedirect->setPath('*/*/index');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__("We can't find a system to delete."));
        return $resultRedirect->setPath('*/*/index');
    }
}
