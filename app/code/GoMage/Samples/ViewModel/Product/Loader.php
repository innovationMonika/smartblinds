<?php declare(strict_types=1);

namespace GoMage\Samples\ViewModel\Product;

use GoMage\Samples\Model\Config;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

class Loader
{
    private CollectionFactory $collectionFactory;
    private Config $config;

    public function __construct(
        CollectionFactory $collectionFactory,
        Config $config
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->config = $config;
    }

    public function loadProducts(array $ids): Collection
    {
        $collection = $this->collectionFactory->create();
        $collection->joinUrlRewrite();
        $collection->addAttributeToSelect([
            'color',
            'transparency',
            'swatch_image',
            'name',
            'system_category',
            $this->config->getProductImageAttribute()
        ]);
        $collection->addAttributeToFilter('entity_id', ['in' => $ids]);
        $collection->getSelect()->group('entity_id');
        return $collection;
    }
}
