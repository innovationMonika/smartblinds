<?php declare(strict_types=1);

namespace Smartblinds\System\Controller\Adminhtml\System;

use Exception;
use GoMage\Ui\Model\EntityRegistry;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Smartblinds\System\Model\Registry;
use Smartblinds\System\Model\ResourceModel\System as SystemResource;
use Smartblinds\System\Model\ResourceModel\System\CollectionFactory;
use Smartblinds\System\Model\SystemFactory;

class MassDelete extends Base
{
    private Filter $filter;
    private CollectionFactory $collectionFactory;

    public function __construct(
        Filter $filter,
        CollectionFactory $collectionFactory,
        SystemResource $systemResource,
        SystemFactory $systemFactory,
        EntityRegistry $registry,
        Context $context
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($systemResource, $systemFactory, $registry, $context);
    }

    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();

        $deletedItems = 0;
        foreach ($collection as $item) {
            try {
                $this->systemResource->delete($item);
                $deletedItems++;
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        if (!$collectionSize) {
            return $resultRedirect->setPath('*/*/index');
        }

        if ($collectionSize !== $deletedItems) {
            $this->messageManager->addErrorMessage(
                __('Failed to delete %1 system(s)', $collectionSize - $deletedItems)
            );
        }

        $this->messageManager->addSuccessMessage(
            __('A total of %1 system(s) have been deleted', $deletedItems)
        );

        return $resultRedirect->setPath('*/*/index');
    }
}
