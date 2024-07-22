<?php
declare(strict_types=1);

namespace GoMage\CatalogBlocks\Setup\Patch\Data;

use Magento\Catalog\Model\Category\Attribute\Source\Page;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Frontend\Image as ImageFrontendModel;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddAdditionalBlocksAttributes implements DataPatchInterface
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
            'text_image_left',
            [
                'type' => 'int',
                'label' => 'CMS Block Text and image on the left',
                'input' => 'select',
                'source' => Page::class,
                'required' => false,
                'sort_order' => 20,
                'group' => 'Content',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
            ]
        );
        $eavSetup->addAttribute(
            Product::ENTITY,
            'two_columns',
            [
                'type' => 'int',
                'label' => 'CMS Block Two columns',
                'input' => 'select',
                'source' => Page::class,
                'required' => false,
                'sort_order' => 30,
                'group' => 'Content',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
            ]
        );
        $eavSetup->addAttribute(
            Product::ENTITY,
            'three_columns',
            [
                'type' => 'int',
                'label' => 'CMS Block Three columns',
                'input' => 'select',
                'source' => Page::class,
                'required' => false,
                'sort_order' => 40,
                'group' => 'Content',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
            ]
        );
        $eavSetup->addAttribute(
            Product::ENTITY,
            'text_image_right',
            [
                'type' => 'int',
                'label' => 'CMS Block Text and image on the right',
                'input' => 'select',
                'source' => Page::class,
                'required' => false,
                'sort_order' => 50,
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
