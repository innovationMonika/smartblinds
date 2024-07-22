<?php declare(strict_types=1);

namespace Smartblinds\System\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddAlternateSystemTypeAttribute implements DataPatchInterface
{
    private ModuleDataSetupInterface $moduleDataSetup;
    private EavSetupFactory $eavSetupFactory;
    private Json $json;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        Json $json
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->json = $json;
    }

    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $attributeCode = 'system_type_alternate';

        $eavSetup->addAttribute(
            Product::ENTITY,
            $attributeCode,
            [
                'type'         => 'int',
                'label'        => 'Alternate System Type',
                'input'        => 'select',
                'required'     => false,
                'sort_order'   => 1000,
                'global'       => ScopedAttributeInterface::SCOPE_GLOBAL,
                'source'       => Table::class,
                'apply_to'     => 'simple,virtual,configurable',
                'user_defined' => 1,
                'used_in_product_listing' => true
            ]
        );

        $eavSetup->updateAttribute(
            Product::ENTITY,
            $attributeCode,
            'additional_data',
            $this->json->serialize([
                'swatch_input_type' => 'visual',
                'update_product_preview_image' => '0',
                'use_product_image_for_swatch' => '0'
            ])
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
