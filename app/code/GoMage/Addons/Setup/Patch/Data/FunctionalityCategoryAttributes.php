<?php

namespace GoMage\Addons\Setup\Patch\Data;

use Magento\Catalog\Model\Category;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class FunctionalityCategoryAttributes implements DataPatchInterface
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
            'func_systeem',
            [
                'type' => 'text',
                'label' => 'Systeem',
                'input' => 'select',
                'required' => false,
                'sort_order' => 50,
                'group' => 'Product Info Tabs',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'option' => ['values' => []],
            ]
        );
        $eavSetup->addAttribute(
            Category::ENTITY,
            'func_stof',
            [
                'type' => 'text',
                'label' => 'Stof',
                'input' => 'select',
                'required' => false,
                'sort_order' => 50,
                'group' => 'Product Info Tabs',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'option' => ['values' => []],
            ]
        );
        $eavSetup->addAttribute(
            Category::ENTITY,
            'func_garantie',
            [
                'type' => 'text',
                'label' => 'Garantie',
                'input' => 'select',
                'required' => false,
                'sort_order' => 50,
                'group' => 'Product Info Tabs',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'option' => ['values' => []],
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
