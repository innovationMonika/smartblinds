<?php declare(strict_types=1);

namespace GoMage\PaidCustomerGroup\Model\ResourceModel;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;

class CustomerPaidTotal
{
    private OrderResource $orderResource;

    public function __construct(OrderResource $orderResource)
    {
        $this->orderResource = $orderResource;
    }

    public function loadAmount(int $customerId): float
    {
        $connection = $this->orderResource->getConnection();
        $select = $connection->select()
            ->from($this->orderResource->getMainTable(), [new \Zend_Db_Expr('SUM(base_total_paid)')])
            ->where(OrderInterface::CUSTOMER_ID . ' = ?', $customerId)
            ->group(OrderInterface::CUSTOMER_ID);
        return (float) $connection->fetchOne($select);
    }
}
