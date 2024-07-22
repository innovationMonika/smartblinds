<?php declare(strict_types=1);

namespace Smartblinds\CatalogSearch\Model\Adapter\BatchDataMapper;

use Magento\AdvancedSearch\Model\Adapter\DataMapper\AdditionalFieldsProviderInterface;
use Magento\Catalog\Model\ResourceModel\Product;

class TypeIdFieldProvider implements AdditionalFieldsProviderInterface
{
    private Product $productResource;

    public function __construct(Product $productResource)
    {
        $this->productResource = $productResource;
    }

    public function getFields(array $productIds, $storeId)
    {
        $connection = $this->productResource->getConnection();
        $select = $connection->select()
            ->from($this->productResource->getEntityTable(), ['entity_id', 'type_id'])
            ->where('entity_id IN (?)', $productIds);
        $rows = $connection->fetchPairs($select);
        $fields = [];
        foreach ($rows as $productId => $typeId) {
            $fields[$productId] = ['type_id' => $typeId];
        }
        return $fields;
    }
}
