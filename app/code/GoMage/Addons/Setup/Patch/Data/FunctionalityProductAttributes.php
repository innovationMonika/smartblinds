<?php

namespace GoMage\Addons\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class FunctionalityProductAttributes implements DataPatchInterface
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
            'func_systeem',
            [
                'type' => 'text',
                'label' => 'Systeem',
                'input' => 'select',
                'required' => false,
                'sort_order' => 100,
                'group' => 'Content',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'apply_to' => 'configurable',
                'option' => ['values' => []],
            ]
        );
        $eavSetup->addAttribute(
            Product::ENTITY,
            'func_stof',
            [
                'type' => 'text',
                'label' => 'Stof',
                'input' => 'select',
                'required' => false,
                'sort_order' => 110,
                'group' => 'Content',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'apply_to' => 'configurable',
                'option' => ['values' => []],
            ]
        );
        $eavSetup->addAttribute(
            Product::ENTITY,
            'func_garantie',
            [
                'type' => 'text',
                'label' => 'Garantie',
                'input' => 'select',
                'required' => false,
                'sort_order' => 120,
                'group' => 'Content',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'apply_to' => 'configurable',
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
