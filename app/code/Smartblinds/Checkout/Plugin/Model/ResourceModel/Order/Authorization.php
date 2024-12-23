<?php

namespace Smartblinds\Checkout\Plugin\Model\ResourceModel\Order;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order as ResourceOrder;
use Smartblinds\Checkout\Model\Config;

class Authorization
{
    private UserContextInterface $userContext;
    private Config $config;

    public function __construct(
        UserContextInterface $userContext,
        Config $config
    ) {
        $this->userContext = $userContext;
        $this->config = $config;
    }

    public function afterLoad(
        ResourceOrder $subject,
        ResourceOrder $result,
        AbstractModel $order
    ) {
        if ($order instanceof Order) {
            if (!$this->isAllowed($order)) {
                throw NoSuchEntityException::singleField('orderId', $order->getId());
            }
        }
        return $result;
    }

    protected function isAllowed(Order $order)
    {

        if (in_array($this->userContext->getUserId(), $this->config->getSupportCustomerIds())) {
            return true;
        }

        return $this->userContext->getUserType() == UserContextInterface::USER_TYPE_CUSTOMER
            ? $order->getCustomerId() == $this->userContext->getUserId()
            : true;
    }
}
