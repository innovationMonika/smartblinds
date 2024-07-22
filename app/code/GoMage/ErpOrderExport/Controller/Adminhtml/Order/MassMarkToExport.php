<?php

namespace GoMage\ErpOrderExport\Controller\Adminhtml\Order;

use GoMage\ErpOrderExport\Model\Source\Status;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction;

class MassMarkToExport extends AbstractMassAction implements HttpPostActionInterface
{
    private OrderRepositoryInterface $orderRepository;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->orderRepository = $orderRepository;
    }

    protected function massAction(AbstractCollection $collection)
    {
        $count = 0;
        foreach ($collection->getItems() as $order) {
            $order->setData('smartblinds_export_status', Status::UNEXPORTED);
            try {
                $this->orderRepository->save($order);
                $count++;
            } catch (\Exception $e) {}
        }
        $notSetCount = $collection->count() - $count;

        if ($notSetCount && $count) {
            $this->messageManager->addErrorMessage(__('%1 order(s) weren\'t set to export.', $notSetCount));
        } elseif ($notSetCount) {
            $this->messageManager->addErrorMessage(__('Failed to set order(s) to export.'));
        }

        if ($count) {
            $this->messageManager->addSuccessMessage(__('We set %1 order(s) to export.', $count));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }
}
