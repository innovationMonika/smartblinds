<?php

namespace GoMage\ErpOrderExport\Controller\Adminhtml\Order;

use GoMage\ErpOrderExport\Model\Source\Status;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Controller\Adminhtml\Order;

class Export extends Order implements HttpPostActionInterface, HttpGetActionInterface
{
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $order = $this->_initOrder();
        if (!$order) {
            return $resultRedirect->setPath('sales/*/');
        }

        ObjectManager::getInstance()
            ->get(\GoMage\ErpOrderExport\Model\OrderExport::class)
            ->sendOrder($order);
        try {
            $id = $this->getRequest()->getParam('order_id');
            $order = $this->orderRepository->get($id);
            if ($order->getData('smartblinds_registration_status') == Status::ACCEPTED) {
                $this->messageManager->addSuccessMessage(__('You exported the order.'));
            } else if ($order->getData('smartblinds_registration_status') == Status::REJECTED) {
                $this->messageManager->addSuccessMessage(__('Order was rejected.'));
            } else {
                $this->messageManager->addSuccessMessage(__('Failed to export the order.'));
            }
        } catch (\Exception $e) {}
        return $resultRedirect->setPath('sales/order/view', ['order_id' => $order->getId()]);
    }
}
