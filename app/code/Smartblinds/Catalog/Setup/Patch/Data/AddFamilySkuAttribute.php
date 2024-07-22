<?php declare(strict_types=1);

namespace Smartblinds\Catalog\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddFamilySkuAttribute implements DataPatchInterface
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
        $attributeCode = 'family_sku';

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addAttribute(
            Product::ENTITY,
            $attributeCode,
            [
                'type'         => 'varchar',
                'label'        => 'Family Sku',
                'input'        => 'text',
                'required'     => false,
                'sort_order'   => 1000,
                'global'       => ScopedAttributeInterface::SCOPE_GLOBAL,
                'group'        => 'Additional Attributes',
                'apply_to'     => 'simple,configurable',
                'user_defined' => 1,
                'used_in_product_listing' => true
            ]
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
