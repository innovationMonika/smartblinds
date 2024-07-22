<?php declare(strict_types=1);

namespace Smartblinds\System\Setup\Patch\Data;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateSystemTypeAttributeV1 implements DataPatchInterface
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

        $attributes = [
            'system_type' => [
                'options' => [
                    'TDBU',
                    'BU'
                ]
            ],
        ];

        foreach ($attributes as $attributeCode => $config) {
            $entityTypeId = ProductAttributeInterface::ENTITY_TYPE_CODE;
            $attributeId = $eavSetup->getAttributeId($entityTypeId, $attributeCode);
            $eavSetup->addAttributeOption([
                'attribute_id' => $attributeId,
                'values' => $config['options']
            ]);
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
