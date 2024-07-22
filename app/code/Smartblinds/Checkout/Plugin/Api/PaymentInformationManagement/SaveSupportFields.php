<?php

namespace Smartblinds\Checkout\Plugin\Api\PaymentInformationManagement;

use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Smartblinds\Checkout\Model\Source\OrderTypes;

class SaveSupportFields
{
    private OrderResource $orderResource;
    private OrderTypes $orderTypes;
    private OrderFactory $orderFactory;

    public function __construct(
        OrderResource $orderResource,
        OrderTypes $orderTypes,
        OrderFactory $orderFactory
    ) {
        $this->orderResource = $orderResource;
        $this->orderTypes = $orderTypes;
        $this->orderFactory = $orderFactory;
    }

    public function beforeSavePaymentInformationAndPlaceOrder(
        PaymentInformationManagementInterface $subject,
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        $extensionAttributes = $paymentMethod->getExtensionAttributes();

        if (!$extensionAttributes) {
            return [$cartId, $paymentMethod, $billingAddress];
        }

        if ($baseIncrementId = $extensionAttributes->getBaseIncrementId()) {
            try {
                $order = $this->orderFactory->create();
                $couldNotSaveException = new CouldNotSaveException(__('Order with this increment id not found'));
                $this->orderResource->load($order, $baseIncrementId, 'increment_id');
                if (!$order->getId()) {
                    throw $couldNotSaveException;
                }
            } catch (\Exception $e) {
                throw $couldNotSaveException;
            }
        }

        return [$cartId, $paymentMethod, $billingAddress];
    }

    public function afterSavePaymentInformationAndPlaceOrder(
        PaymentInformationManagementInterface $subject,
        $result,
        $cartId,
        PaymentInterface $paymentMethod
    ) {
        $extensionAttributes = $paymentMethod->getExtensionAttributes();

        $updateData = [];

        if ($extensionAttributes) {
            $orderType = $extensionAttributes->getOrderType();
            if (!$orderType || !in_array($orderType, $this->orderTypes->getValues())) {
                $orderType = OrderTypes::ORDER_TYPE_REGULAR;
            }
            $updateData['order_type'] = $orderType;

            if ($baseIncrementId = $extensionAttributes->getBaseIncrementId()) {
                $updateData['base_increment_id'] = $baseIncrementId;
            }
        }

        if ($updateData) {
            $this->orderResource
                ->getConnection()
                ->update(
                    $this->orderResource->getMainTable(),
                    $updateData,
                    ['entity_id = ?' => $result]
                );
        }

        return $result;
    }
}
