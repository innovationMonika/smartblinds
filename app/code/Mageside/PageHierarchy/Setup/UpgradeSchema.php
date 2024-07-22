<?php
/**
 * Copyright © 2018 Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */

namespace Mageside\PageHierarchy\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * Upgrade Data script
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * UpgradeSchema constructor.
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $setup->getConnection()->addColumn(
                $setup->getTable('cms_page'),
                'parent_page_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => 6,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Parent Page ID'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('cms_page'),
                'show_menu_hierarchy',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Show Menu Hierarchy'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('cms_page'),
                'include_in_menu_hierarchy',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Include In Menu Hierarchy'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.4.0') < 0) {
            $setup->getConnection()->addColumn(
                $setup->getTable('cms_page'),
                'hr_sort_order',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => 6,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Hierarchy Sort Order'
                ]
            );
        }

        $setup->endSetup();
    }
}
