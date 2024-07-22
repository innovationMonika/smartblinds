<?php declare(strict_types=1);

namespace Smartblinds\Catalog\Model\ResourceModel\RelativeData\Updater;

use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Smartblinds\Catalog\Model\Config;

class FamilySku implements UpdaterInterface
{
    private ProductResource $productResource;
    private CollectionFactory $collectionFactory;
    private Config $config;
    private Configurable $configurable;

    public function __construct(
        ProductResource $productResource,
        CollectionFactory $collectionFactory,
        Config $config,
        Configurable $configurable
    ) {
        $this->productResource = $productResource;
        $this->collectionFactory = $collectionFactory;
        $this->config = $config;
        $this->configurable = $configurable;
    }

    public function update()
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection
            ->addAttributeToSelect(['family_sku'])
            ->addAttributeToFilter('family_sku', ['in' => $this->getFamilySkus()])
            ->addAttributeToFilter('type_id', ['eq' => Configurable::TYPE_CODE]);

        $insertData = [];

        $familySkuAttribute = $this->productResource->getAttribute('family_sku');
        $familySkuAttributeId = $familySkuAttribute->getAttributeId();
        foreach ($collection as $item) {
            $row = $this->getConfigRow($item);
            if (!$row) {
                continue;
            }
            $childrenIds = $this->configurable->getChildrenIds($item['entity_id']);
            $childrenIds = reset($childrenIds);
            $childrenIds = is_array($childrenIds) ? $childrenIds : [];
            if (!$childrenIds) {
                continue;
            }
            /** @var Collection $collection */
            $childCollection = $this->collectionFactory->create();
            $childCollection->addAttributeToSelect('family_sku');
            $childCollection->addAttributeToFilter('entity_id', ['in' => $childrenIds]);
            foreach ($childCollection as $child) {
                if ($child->getData('family_sku') == $row['family_sku']) {
                    continue;
                }
                $insertData[] = [
                    'attribute_id' => $familySkuAttributeId,
                    'store_id'     => 0,
                    'entity_id'    => $child->getId(),
                    'value'        => $row['family_sku']
                ];
            }
        }

        if ($insertData) {
            $this->productResource
                ->getConnection()
                ->insertOnDuplicate($familySkuAttribute->getBackendTable(), $insertData);
        }
    }

    private function getConfigRow($item): ?array
    {
        foreach ($this->config->getRelativeConfig() as $row) {
            $rowFamilySku = $row['family_sku'] ?? null;
            if ($rowFamilySku == $item->getData('family_sku')) {
                return $row;
            }
        }
        return null;
    }

    private function getFamilySkus(): array
    {
        return array_unique(array_filter(array_map(function ($row) {
            return $row['family_sku'] ?? null;
        }, $this->config->getRelativeConfig())));
    }
}
