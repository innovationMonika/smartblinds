<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Export\RowCustomizer;

use Amasty\Feed\Model\Export\Product as ExportProduct;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;
use Magento\Framework\DB\Select;

class Taxpercents implements RowCustomizerInterface
{
    /**
     * @var \Magento\Tax\Model\Calculation
     */
    private $calculation;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Amasty\Feed\Model\Export\Product
     */
    private $export;

    /**
     * @var array
     */
    private $taxes = [];

    public function __construct(
        ExportProduct $export,
        \Magento\Tax\Model\Calculation $calculation,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
        $this->export = $export;
        $this->calculation = $calculation;
    }

    /**
     * @inheritdoc
     */
    public function prepareData($collection, $productIds)
    {
        $prefixOtherAttributes = ExportProduct::PREFIX_OTHER_ATTRIBUTES;
        if ($this->export->hasAttributes($prefixOtherAttributes)) {
            $productCollection = $this->prepareProductCollection($collection);
            $storeId = $collection->getStoreId();
            $items = $productCollection->getConnection()->fetchPairs($productCollection->getSelect());
            foreach ($items as $entityId => $taxClassId) {
                $addressRequestObject
                    = $this->calculation->getDefaultRateRequest($storeId);
                $addressRequestObject->setProductClassId(
                    $taxClassId
                );

                $this->taxes[$entityId] = $this->calculation->getRate(
                    $addressRequestObject
                );
            }
        }
    }

    private function prepareProductCollection(Collection $collection): Collection
    {
        $productCollection = clone $collection;
        $productCollection->clear();
        $productCollection->applyFrontendPriceLimitations();
        $productCollection->getSelect()->reset(Select::COLUMNS)
            ->columns(['e.entity_id', 'price_index.tax_class_id']);

        return $productCollection;
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
        $customData = &$dataRow['amasty_custom_data'];

        $taxPercent = "0";
        if (isset($this->taxes[$productId]) && $this->taxes[$productId]) {
            $notForamttedTaxpercent = $this->taxes[$productId];
            $taxPercent = sprintf("%0.2f", $notForamttedTaxpercent);
        }

        $customData[ExportProduct::PREFIX_OTHER_ATTRIBUTES]['tax_percents']
            = (string)$taxPercent;

        return $dataRow;
    }

    /**
     * @inheritdoc
     */
    public function getAdditionalRowsCount($additionalRowsCount, $productId)
    {
        return $additionalRowsCount;
    }
}
