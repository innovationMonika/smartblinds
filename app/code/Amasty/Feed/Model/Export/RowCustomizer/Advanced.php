<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Export\RowCustomizer;

use Amasty\Feed\Model\Export\Product as Export;
use Amasty\Feed\Model\ResourceModel\ProductCategoriesProvider;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\Framework\App\ObjectManager;

class Advanced implements RowCustomizerInterface
{
    public const ATTRIBUTES = [
        'category_ids' => 'Category Ids',
    ];

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @var Export
     */
    private $export;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductCategoriesProvider
     */
    private $productCategoriesProvider;

    /**
     * Storage for product categories [product_id => category_ids]
     *
     * @var array
     */
    private $productCategoryMapping = [];

    public function __construct(
        Export $export,
        ProductRepositoryInterface $productRepository,
        ?StockItemRepositoryInterface $stockItemRepository, //@deprecated
        ProductCategoriesProvider $productCategoriesProvider = null
    ) {
        $this->export = $export;
        $this->productRepository = $productRepository;
        $this->productCategoriesProvider = $productCategoriesProvider
            ?? ObjectManager::getInstance()->get(ProductCategoriesProvider::class);
    }

    /**
     * @inheritdoc
     */
    public function prepareData($collection, $productIds)
    {
        if ($this->export->hasAttributes(Export::PREFIX_ADVANCED_ATTRIBUTE)) {
            $this->attributes = $this->export->getAttributesByType(Export::PREFIX_ADVANCED_ATTRIBUTE);
            $productCategories = $this->productCategoriesProvider->getCategoryIds($productIds);
            $productCategories += array_fill_keys($productIds, null);
            ksort($productCategories);

            $this->productCategoryMapping = $productCategories;
        }
    }

    /**
     * @inheritdoc
     */
    public function addHeaderColumns($columns)
    {
        return $columns;
    }

    /**
     * @inheritdoc
     */
    public function addData($dataRow, $productId)
    {
        $dataRow['amasty_custom_data'][Export::PREFIX_ADVANCED_ATTRIBUTE] = [];

        foreach ($this->attributes as $attribute) {
            $result = '';

            switch ($attribute) {
                case 'category_ids':
                    $result = $this->getCategoryIds($productId);
                    break;
            }
            $dataRow['amasty_custom_data'][Export::PREFIX_ADVANCED_ATTRIBUTE][$attribute] = $result;
        }

        return $dataRow;
    }

    /**
     * @param int $productId
     *
     * @return string
     */
    private function getCategoryIds($productId)
    {
        if (!empty($this->productCategoryMapping[$productId])) {
            return $this->productCategoryMapping[$productId];
        }

        $product = $this->productRepository->getById($productId);
        $categoryIds = $product->getCategoryIds();

        return implode(",", $categoryIds);
    }

    /**
     * @inheritdoc
     */
    public function getAdditionalRowsCount($additionalRowsCount, $productId)
    {
        return $additionalRowsCount;
    }
}
