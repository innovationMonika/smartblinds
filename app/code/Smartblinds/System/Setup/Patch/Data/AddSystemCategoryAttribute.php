<?php declare(strict_types=1);

namespace Smartblinds\System\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Smartblinds\System\Model\Product\Attribute\Source\SystemCategory;

class AddSystemCategoryAttribute implements DataPatchInterface
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
            'system_category' => [
                'label' => 'System Category'
            ]
        ];

        foreach ($attributes as $attributeCode => $config) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                $attributeCode,
                [
                    'type'         => 'varchar',
                    'label'        => $config['label'],
                    'input'        => 'select',
                    'required'     => false,
                    'sort_order'   => 1000,
                    'global'       => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'source'       => SystemCategory::class,
                    'apply_to'     => 'simple,virtual,configurable',
                    'user_defined' => 1,
                    'used_in_product_listing' => true
                ]
            );
        }

        $this->setDefaultAttributeValue($eavSetup);
    }

    private function setDefaultAttributeValue(EavSetup $eavSetup)
    {
        $systemCategoryAttributeId = $eavSetup
            ->getAttributeId(Product::ENTITY, 'system_category');
        $systemCategoryAttributeTable = $eavSetup
            ->getAttributeTable(Product::ENTITY, 'system_category');

        $connection = $this->moduleDataSetup->getConnection();
        $select = $connection->select()
            ->from('catalog_product_entity', 'entity_id')
            ->where('type_id IN (?)', ['simple', 'configurable']);
        $configurableIds = $connection->fetchCol($select);
        $data = array_map(function ($id) use ($systemCategoryAttributeId) {
            return [
                'attribute_id' => $systemCategoryAttributeId,
                'store_id' => 0,
                'entity_id' => $id,
                'value' => SystemCategory::ROLLER
            ];
        }, $configurableIds);
        if ($data) {
            $connection->insertOnDuplicate($systemCategoryAttributeTable, $data);
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
