<?php declare(strict_types=1);

namespace Smartblinds\System\Controller\Adminhtml\System;

use Magento\Backend\Model\View\Result\Redirect;
use Smartblinds\System\Model\SystemFactory;
use Smartblinds\System\Model\ResourceModel\System as SystemResource;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class Copy extends Action
{

   protected $systemFactory;
    protected $systemResource;

    public function __construct(
        Context $context,
        SystemFactory $systemFactory,
        SystemResource $systemResource
    ) {
        $this->systemFactory = $systemFactory;
        $this->systemResource = $systemResource;
        parent::__construct($context);

    }
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        echo $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                // Initialize the system model
                $system = $this->systemFactory->create();
                $this->systemResource->load($system, $id);

                // Get data and unset the ID to create a new entity
                $newData = $system->getData();
                unset($newData['id']);

                // Create a new system model and add the new data
                $newSystem = $this->systemFactory->create();
                $newSystem->addData($newData);

                // Save the new system model
                $this->systemResource->save($newSystem);
                $this->messageManager->addSuccessMessage(__('You copied the system.'));
                return $resultRedirect->setPath('*/*/index');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage() );
                return $resultRedirect->setPath('*/*');
            }
        }
        $this->messageManager->addErrorMessage(__("We can't find a system to Copy."));
        return $resultRedirect->setPath('*/*/index');
    }
}
