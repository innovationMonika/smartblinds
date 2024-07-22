<?php declare(strict_types=1);

namespace Smartblinds\ConfigurableProduct\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Backend\Price;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddSmartblindsPriceAttribute implements DataPatchInterface
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
        $attributeCode = 'smartblinds_price';

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addAttribute(
            Product::ENTITY,
            $attributeCode,
            [
                'type' => 'decimal',
                'label' => 'Smartblinds Price',
                'input' => 'price',
                'backend' => Price::class,
                'sort_order' => 1000,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'searchable' => false,
                'filterable' => false,
                'visible_in_advanced_search' => false,
                'used_in_product_listing' => true,
                'used_for_sort_by' => false,
                'apply_to' => 'configurable',
                'group' => 'Prices',
                'required' => false
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
