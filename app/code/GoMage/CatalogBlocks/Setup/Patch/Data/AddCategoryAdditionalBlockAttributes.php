<?php
declare(strict_types=1);

namespace GoMage\CatalogBlocks\Setup\Patch\Data;

use Magento\Catalog\Model\Category\Attribute\Source\Page;
use Magento\Catalog\Model\Category;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddCategoryAdditionalBlockAttributes implements DataPatchInterface
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
            Category::ENTITY,
            'meten',
            [
                'type' => 'int',
                'label' => 'Meten',
                'input' => 'select',
                'source' => Page::class,
                'required' => false,
                'sort_order' => 20,
                'group' => 'Product Info Tabs',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
            ]
        );
        $eavSetup->addAttribute(
            Category::ENTITY,
            'monteren',
            [
                'type' => 'int',
                'label' => 'Monteren',
                'input' => 'select',
                'source' => Page::class,
                'required' => false,
                'sort_order' => 30,
                'group' => 'Product Info Tabs',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
            ]
        );
        $eavSetup->addAttribute(
            Category::ENTITY,
            'vragen',
            [
                'type' => 'int',
                'label' => 'Veelgestelde Vragen',
                'input' => 'select',
                'source' => Page::class,
                'required' => false,
                'sort_order' => 40,
                'group' => 'Product Info Tabs',
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
