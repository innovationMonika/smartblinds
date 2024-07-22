<?php

namespace GoMage\Samples\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Sales\Model\ResourceModel\Order;

class SetIsSamplesFlag implements DataPatchInterface
{
    private Order $orderResource;

    public function __construct(Order $orderResource)
    {
        $this->orderResource = $orderResource;
    }

    public function apply()
    {
        $connection = $this->orderResource->getConnection();

        $connection
            ->update(
                $this->orderResource->getMainTable(),
                ['is_samples_order' => 1],
                ['status = ?' => 'samples']
            );

        $select = $connection
            ->select()
            ->from('sales_order_address', 'parent_id')
            ->where(new \Zend_Db_Expr('samples_form_id IS NOT NULL'));

        $orderIds = $connection->fetchCol($select);

        $connection
            ->update(
                $this->orderResource->getMainTable(),
                ['is_samples_order' => 1],
                ['entity_id IN (?)' => $orderIds]
            );
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
