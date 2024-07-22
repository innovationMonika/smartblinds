<?php declare(strict_types=1);

namespace Smartblinds\Catalog\Model\ResourceModel\RelativeData\Updater;

use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Smartblinds\Catalog\Model\Config;

class ColorGroup implements UpdaterInterface
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
            ->addAttributeToSelect(['color', 'color_group'])
            ->addAttributeToFilter('color', ['in' => $this->getColorOptionIds()])
            ->addAttributeToFilter('type_id', ['eq' => Type::TYPE_SIMPLE]);

        $insertData = [];

        $colorMap = $this->getColorMap();
        $colorGroupAttribute = $this->productResource->getAttribute('color_group');
        $colorGroupAttributeId = $colorGroupAttribute->getAttributeId();
        foreach ($collection as $item) {
            $color = $item->getData('color');
            $colorGroup = $item->getData('color_group');
            $colorGroupToSet = $colorMap[$color];
            if ($colorGroupToSet && $colorGroupToSet != $colorGroup) {
                $insertData[] = [
                    'attribute_id' => $colorGroupAttributeId,
                    'store_id'     => 0,
                    'entity_id'    => $item->getId(),
                    'value'        => $colorGroupToSet
                ];
            }
        }

        if ($insertData) {
            $this->productResource
                ->getConnection()
                ->insertOnDuplicate($colorGroupAttribute->getBackendTable(), $insertData);
        }
    }

    private function getColorOptionIds(): array
    {
        return array_filter(array_map(function ($row) {
            return $row['color'] ?? null;
        }, $this->config->getColorGroupConfig()));
    }

    private function getColorMap(): array
    {
        $map = [];
        foreach ($this->config->getColorGroupConfig() as $row) {
            $map[$row['color']] = $row['color_group'];
        }
        return $map;
    }
}
