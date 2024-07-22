<?php declare(strict_types=1);

namespace Smartblinds\Catalog\Model\ResourceModel\RelativeData\Updater;

use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Smartblinds\Catalog\Model\Config;

class SmartblindsSku implements UpdaterInterface
{
    private ProductResource $productResource;
    private CollectionFactory $collectionFactory;
    private Config $config;

    public function __construct(
        ProductResource $productResource,
        CollectionFactory $collectionFactory,
        Config $config
    ) {
        $this->productResource = $productResource;
        $this->collectionFactory = $collectionFactory;
        $this->config = $config;
    }

    public function update()
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection
            ->addAttributeToSelect(['color', 'family_sku', 'transparency', 'fabric_size', 'smartblinds_sku'])
            ->addAttributeToFilter('color', ['in' => $this->getColorOptionIds()])
            ->addAttributeToFilter('family_sku', ['in' => $this->getFamilySkus()])
            ->addAttributeToFilter('type_id', ['eq' => Type::TYPE_SIMPLE]);

        $insertData = [];

        $smartblindsSkuAttribute = $this->productResource->getAttribute('smartblinds_sku');
        $smartblindsSkuAttributeId = $smartblindsSkuAttribute->getAttributeId();
        foreach ($collection as $item) {
            $row = $this->getConfigRow($item);
            if (!$row || $row['smartblinds_sku'] == $item->getData('smartblinds_sku')) {
                continue;
            }
            $insertData[] = [
                'attribute_id' => $smartblindsSkuAttributeId,
                'store_id'     => 0,
                'entity_id'    => $item['entity_id'],
                'value'        => $row['smartblinds_sku']
            ];
        }

        if ($insertData) {
            $this->productResource
                ->getConnection()
                ->insertOnDuplicate($smartblindsSkuAttribute->getBackendTable(), $insertData);
        }
    }

    private function getConfigRow($item): ?array
    {
        $itemFamilySku = $item->getData('family_sku');
        $itemColor = $item->getData('color');
        $itemTransparency = $item->getData('transparency');
        $itemFabricSize = $item->getData('fabric_size');
        foreach ($this->config->getRelativeConfig() as $row) {
            $rowFamilySku = $row['family_sku'] ?? null;
            $rowColor = $row['color'] ?? null;
            $rowTransparency = $row['transparency'] ?? null;
            $rowFabricSize = $row['fabric_size'] ?? null;
            if ($rowFamilySku == $itemFamilySku &&
                $rowColor == $itemColor &&
                $rowTransparency == $itemTransparency &&
                $rowFabricSize == $itemFabricSize)
            {
                return $row;
            }
        }
        return null;
    }

    private function getColorOptionIds(): array
    {
        return array_filter(array_map(function ($row) {
            return $row['color'] ?? null;
        }, $this->config->getRelativeConfig()));
    }

    private function getFamilySkus(): array
    {
        return array_filter(array_map(function ($row) {
            return $row['family_sku'] ?? null;
        }, $this->config->getRelativeConfig()));
    }
}
