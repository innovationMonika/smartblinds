<?php declare(strict_types=1);

namespace GoMage\ErpOrderExport\Plugin\Sales\Block\Adminhtml\Order\View;

use GoMage\ErpOrderExport\Model\Source\Status;
use Magento\Sales\Block\Adminhtml\Order\View;
use Magento\Sales\Model\Order;

class AddExportButton
{
    public function beforeSetLayout(
        View $subject
    ) {
        $order = $subject->getOrder();
        if (!$order && !$order->getId()) {
            return;
        }

        if (in_array($order->getStatus(), [
            Order::STATE_COMPLETE,
            Order::STATE_CLOSED,
            Order::STATE_CANCELED,
            'samples'
        ])) {
            return;
        }

        if ($order->getData('smartblinds_registration_status') == Status::ACCEPTED) {
            return;
        }

        $subject->addButton(
            'export_order',
            [
                'label' => __('Export Order'),
                'onclick' => 'setLocation(\'' . $subject
                        ->getUrl('sales/order/export') . '\')'
            ]
        );
    }
}
