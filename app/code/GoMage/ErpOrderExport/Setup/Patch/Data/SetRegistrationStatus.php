<?php declare(strict_types=1);

namespace GoMage\ErpOrderExport\Setup\Patch\Data;

use GoMage\ErpOrderExport\Model\Source\Status;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Sales\Model\ResourceModel\Order;

class SetRegistrationStatus implements DataPatchInterface
{
    private Order $orderResource;

    public function __construct(Order $orderResource)
    {
        $this->orderResource = $orderResource;
    }

    public function apply()
    {
        $connection = $this->orderResource->getConnection();
        $connection->update(
            $this->orderResource->getMainTable(),
            ['smartblinds_registration_status' => Status::ACCEPTED],
            new \Zend_Db_Expr('smartblinds_registration_id IS NOT NULL')
        );
        $connection->update(
            $this->orderResource->getMainTable(),
            ['smartblinds_registration_status' => Status::REJECTED],
            new \Zend_Db_Expr('smartblinds_registration_error IS NOT NULL')
        );

        $select = $connection->select()->from(
            $this->orderResource->getMainTable(),
            [
                'entity_id',
                'smartblinds_registration_status'
            ]
        );
        $rows = $connection->fetchAll($select);
        $connection->insertOnDuplicate(
            'sales_order_grid',
            $rows
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
