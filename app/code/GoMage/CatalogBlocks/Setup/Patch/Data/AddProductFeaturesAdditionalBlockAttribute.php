<?php
declare(strict_types=1);

namespace GoMage\CatalogBlocks\Setup\Patch\Data;

use Magento\Catalog\Model\Category\Attribute\Source\Page;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddProductFeaturesAdditionalBlockAttribute implements DataPatchInterface
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

        $eavSetup->addAttribute(
            Product::ENTITY,
            'cms_block_product_features',
            [
                'type' => 'int',
                'label' => 'CMS Block Product Features',
                'input' => 'select',
                'source' => Page::class,
                'required' => false,
                'sort_order' => 45,
                'group' => 'Content',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
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
