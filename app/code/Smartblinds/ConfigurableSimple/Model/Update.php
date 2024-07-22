<?php

namespace Smartblinds\ConfigurableSimple\Model;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurable;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Swatches\Model\ResourceModel\Swatch\Collection as SwatchCollection;
use Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory as SwatchCollectionFactory;

class Update
{
    private CollectionFactory $productCollectionFactory;
    private Configurable $configurableType;
    private SwatchCollectionFactory $swatchCollectionFactory;
    private AttributeRepositoryInterface $attributeRepository;
    private Json $json;
    private ResourceConnection $resourceConnection;

    public function __construct(
        CollectionFactory $productCollectionFactory,
        Configurable $configurableType,
        SwatchCollectionFactory $swatchCollectionFactory,
        AttributeRepositoryInterface $attributeRepository,
        Json $json,
        ResourceConnection $resourceConnection
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->configurableType = $configurableType;
        $this->swatchCollectionFactory = $swatchCollectionFactory;
        $this->attributeRepository = $attributeRepository;
        $this->json = $json;
        $this->resourceConnection = $resourceConnection;
    }

    public function execute()
    {
        $configurableCollection = $this->productCollectionFactory->create();
        $configurableCollection->addFieldToFilter('type_id', ['eq' => TypeConfigurable::TYPE_CODE]);
        $parentIds = $configurableCollection->getColumnValues('entity_id');

        $groupedChildrenIds = [];
        foreach ($parentIds as $parentId) {
            $groupedChildrenIds[$parentId] = array_values($this->configurableType->getChildrenIds($parentId)[0]);
        }
        $childrenIds = [];
        foreach ($groupedChildrenIds as $parentId => $group) {
            $childrenIds = array_merge($childrenIds, $group);
        }

        $simpleCollection = $this->productCollectionFactory->create();
        $simpleCollection->addFieldToFilter('type_id', ['eq' => Type::TYPE_SIMPLE]);
        $simpleCollection->addFieldToFilter('entity_id', ['in' => $childrenIds]);
        $simpleCollection->addAttributeToSelect('color');
        $simpleIdColorMap = [];
        foreach ($simpleCollection as $product) {
            $simpleIdColorMap[$product->getId()] = $product->getData('color');
        }

        $colorIds = array_unique(array_values($simpleIdColorMap));
        /** @var SwatchCollection $swatchCollection */
        $swatchCollection = $this->swatchCollectionFactory->create();
        $swatchCollection->addFilterByOptionsIds($colorIds);
        $colorIdsToSwatchImage = [];
        foreach ($colorIds as $colorId) {
            $swatch = $swatchCollection->getItemByColumnValue('option_id', $colorId);
            if (!$swatch) {
                continue;
            }
            $colorIdsToSwatchImage[$colorId] = $swatch->getValue();
        }

        $childIdColorIds = [];
        foreach ($groupedChildrenIds as $parentId => $group) {
            $colorIds = [];
            foreach ($group as $childId) {
                if (!isset($simpleIdColorMap[$childId])) {
                    continue;
                }
                $colorId = $simpleIdColorMap[$childId];
                if (!isset($colorIdsToSwatchImage[$colorId])) {
                    continue;
                }
                $colorIds[$childId] = $colorId;
            }
            $colorIds = array_flip(array_unique(array_values($colorIds)));
            foreach ($group as $childId) {
                $childColorIds = $colorIds;
                if (isset($simpleIdColorMap[$childId])) {
                    unset($childColorIds[$simpleIdColorMap[$childId]]);
                }
                $childIdColorIds[$childId] = array_values(array_flip($childColorIds));
            }
        }

        $result = [];
        foreach ($childIdColorIds as $childId => $colorIds) {
            $swatches = [];
            foreach ($colorIds as $colorId) {
                if (!isset($colorIdsToSwatchImage[$colorId])) {
                    continue;
                }
                $swatches[] = $colorIdsToSwatchImage[$colorId];
            }
            $firstSwatches = array_slice($swatches, 0, 3);
            $colorsLeft = count($swatches) > 3 ? count($swatches) - 3 : 0;
            $result[$childId] = [
                'colors' => $firstSwatches,
                'leftCount' => $colorsLeft
            ];
        }

        $colorsInfoAttribute = $this->attributeRepository->get(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'colors_info'
        );

        $insertData = [];
        foreach ($result as $childId => $data) {
            $insertData[] = [
                'attribute_id' => $colorsInfoAttribute->getAttributeId(),
                'store_id' => 0,
                'entity_id' => $childId,
                'value' => $this->json->serialize($data)
            ];
        }

        $this->resourceConnection->getConnection()
            ->insertOnDuplicate(
                $colorsInfoAttribute->getBackendTable(),
                $insertData
            );
    }
}
