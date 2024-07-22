<?php declare(strict_types=1);

namespace Smartblinds\ImageImport\Model\ResourceModel\Images\DropValues;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Smartblinds\ImageImport\Model\Csv\SkuLoader;
use Smartblinds\ImageImport\Model\ResourceModel\Images\DropValues;

class Images implements ExecutorInterface
{
    private ResourceConnection $resourceConnection;
    private AttributeRepositoryInterface $attributeRepository;
    private SkuLoader $skuLoader;
    private DropValues $dropValues;

    public function __construct(
        ResourceConnection $resourceConnection,
        AttributeRepositoryInterface $attributeRepository,
        SkuLoader $skuLoader,
        DropValues $dropValues
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->attributeRepository = $attributeRepository;
        $this->skuLoader = $skuLoader;
        $this->dropValues = $dropValues;
    }

    public function execute()
    {
        $rows = $this->loadValuesToDelete();
        $this->dropValues->execute($rows);
    }

    public function loadValuesToDelete(): array
    {
        $swatchImageAttribute = $this->attributeRepository
            ->get(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'swatch_image'
            );

        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(
                ['g' => 'catalog_product_entity_media_gallery'],
                ['gallery_value_id' => 'g.value_id']
            )
            ->joinInner(
                ['link' => 'catalog_product_entity_media_gallery_value_to_entity'],
                'link.value_id = g.value_id',
                []
            )
            ->joinInner(
                ['cpe' => 'catalog_product_entity'],
                'link.entity_id = cpe.entity_id',
                []
            )
            ->joinLeft(
                ['cpev' => 'catalog_product_entity_varchar'],
                'g.value = cpev.value',
                ['varchar_vale_id' => 'cpev.value_id']
            )
            ->where(new \Zend_Db_Expr(
                $connection->quoteInto(
                    'cpev.value_id IS NULL OR cpev.attribute_id != ?',
                    $swatchImageAttribute->getAttributeId()
                )
            ))
            ->where('cpe.sku IN (?)', $this->skuLoader->loadSkus('images'));

        return $connection->fetchPairs($select);
    }
}
