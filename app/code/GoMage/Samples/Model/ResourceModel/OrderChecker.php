<?php declare(strict_types=1);

namespace GoMage\Samples\Model\ResourceModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\ResourceModel\Order;

class OrderChecker
{
    private Order $orderResource;
    private ScopeConfigInterface $scopeConfig;

    public function __construct(
        Order $orderResource,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->orderResource = $orderResource;
        $this->scopeConfig = $scopeConfig;
    }

    public function isSamplesOrderByIncrementId($incrementId)
    {
        return $this->isSamplesOrder($incrementId, OrderInterface::INCREMENT_ID);
    }

    public function isSamplesOrderById($id)
    {
        return $this->isSamplesOrder($id, OrderInterface::ENTITY_ID);
    }

    private function isSamplesOrder($id, string $field)
    {
        $connection = $this->orderResource->getConnection();
        $select = $connection
            ->select()
            ->from($this->orderResource->getMainTable(), 'status')
            ->where($field . ' = ?', $id);
        return $connection->fetchOne($select) === $this->scopeConfig->getValue('order_status');
    }
}
