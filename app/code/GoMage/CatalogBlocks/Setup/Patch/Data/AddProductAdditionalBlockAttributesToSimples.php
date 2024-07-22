<?php declare(strict_types=1);

namespace GoMage\CatalogBlocks\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddProductAdditionalBlockAttributesToSimples implements DataPatchInterface
{
    private ModuleDataSetupInterface $moduleDataSetup;
    private EavSetupFactory $eavSetupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->updateAttribute(
            Product::ENTITY,
            'meten',
            'apply_to',
            'configurable,simple'
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'monteren',
            'apply_to',
            'configurable,simple'
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            'vragen',
            'apply_to',
            'configurable,simple'
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
