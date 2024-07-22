<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_SpecialPromotions
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SpecialPromotions\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 * @package Mageplaza\DeliveryTime\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $connection = $setup->getConnection();
        $salesRuleTable = $setup->getTable('salesrule');
        if (!$connection->tableColumnExists($salesRuleTable, 'mp_enable_coupon_pickup')) {
            $connection->addColumn($salesRuleTable, 'mp_enable_coupon_pickup', [
                'type' => Table::TYPE_INTEGER,
                'length' => '1',
                'nullable' => false,
                'default' => 0,
                'comment' => 'Enable Coupon Pickup'
            ]);
        }
        if (!$connection->tableColumnExists($salesRuleTable, 'mp_product_x_qty')) {
            $connection->addColumn($salesRuleTable, 'mp_product_x_qty', [
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'Product X Qty'
            ]);
        }
        if (!$connection->tableColumnExists($salesRuleTable, 'mp_product_y_qty')) {
            $connection->addColumn($salesRuleTable, 'mp_product_y_qty', [
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'Product Y Qty'
            ]);
        }
        if (!$connection->tableColumnExists($salesRuleTable, 'mp_product_x_actions_serialized')) {
            $connection->addColumn($salesRuleTable, 'mp_product_x_actions_serialized', [
                'type' => Table::TYPE_TEXT,
                'length' => '64K',
                'nullable' => true,
                'comment' => 'Product X Conditions'
            ]);
        }
        if (!$connection->tableColumnExists($salesRuleTable, 'mp_product_y_actions_serialized')) {
            $connection->addColumn($salesRuleTable, 'mp_product_y_actions_serialized', [
                'type' => Table::TYPE_TEXT,
                'length' => '64K',
                'nullable' => true,
                'comment' => 'Product Y Conditions'
            ]);
        }
        if (!$connection->tableColumnExists($salesRuleTable, 'mp_skip_special_tier_price')) {
            $connection->addColumn($salesRuleTable, 'mp_skip_special_tier_price', [
                'type' => Table::TYPE_INTEGER,
                'length' => '1',
                'nullable' => false,
                'default' => 0,
                'comment' => 'Skip Special Price/Tier Price'
            ]);
        }
        if (!$connection->tableColumnExists($salesRuleTable, 'mp_calculate_discount')) {
            $connection->addColumn($salesRuleTable, 'mp_calculate_discount', [
                'type' => Table::TYPE_INTEGER,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Mp Calculate Discount'
            ]);
        }
        if (!$connection->tableColumnExists($salesRuleTable, 'mp_enable_coupon_pickup')) {
            $connection->addColumn($salesRuleTable, 'mp_enable_coupon_pickup', [
                'type' => Table::TYPE_INTEGER,
                'length' => '1',
                'nullable' => false,
                'default' => 0,
                'comment' => 'Mp Enable Coupon Pickup'
            ]);
        }

        $setup->endSetup();
    }
}
