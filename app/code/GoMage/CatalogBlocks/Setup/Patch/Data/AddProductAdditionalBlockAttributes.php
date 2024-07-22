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

class AddProductAdditionalBlockAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private EavSetupFactory $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addAttribute(
            Product::ENTITY,
            'meten',
            [
                'type' => 'int',
                'label' => 'Meten',
                'input' => 'select',
                'source' => Page::class,
                'required' => false,
                'sort_order' => 50,
                'group' => 'Content',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'apply_to' => 'configurable',
            ]
        );
        $eavSetup->addAttribute(
            Product::ENTITY,
            'monteren',
            [
                'type' => 'int',
                'label' => 'Monteren',
                'input' => 'select',
                'source' => Page::class,
                'required' => false,
                'sort_order' => 60,
                'group' => 'Content',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'apply_to' => 'configurable',
            ]
        );
        $eavSetup->addAttribute(
            Product::ENTITY,
            'vragen',
            [
                'type' => 'int',
                'label' => 'Veelgestelde Vragen',
                'input' => 'select',
                'source' => Page::class,
                'required' => false,
                'sort_order' => 70,
                'group' => 'Content',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'apply_to' => 'configurable',
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
