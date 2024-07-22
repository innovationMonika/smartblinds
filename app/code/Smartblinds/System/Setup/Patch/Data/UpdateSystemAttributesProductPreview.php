<?php declare(strict_types=1);

namespace Smartblinds\System\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateSystemAttributesProductPreview implements DataPatchInterface
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

        $attributes = ['system_type', 'system_size', 'system_color'];

        foreach ($attributes as $attribute) {
            $eavSetup->updateAttribute(
                Product::ENTITY,
                $attribute,
                'additional_data',
                $this->json->serialize([
                    'swatch_input_type' => 'visual',
                    'update_product_preview_image' => '1',
                    'use_product_image_for_swatch' => '0'
                ])
            );
        }
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
