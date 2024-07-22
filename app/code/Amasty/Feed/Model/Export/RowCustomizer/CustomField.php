<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Export\RowCustomizer;

use Amasty\Feed\Api\CustomFieldsRepositoryInterface;
use Amasty\Feed\Model\Export\Product as Export;
use Amasty\Feed\Model\Export\Utils\MergedAttributeProcessor;
use Amasty\Feed\Model\Field\CustomFieldsProcessor;
use Amasty\Feed\Model\Field\CustomFieldsValidator;
use Amasty\Feed\Model\Field\CustomFieldsValidatorFactory;
use Amasty\Feed\Model\Field\ResourceModel\CollectionFactory as FieldCollectionFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;
use Magento\Framework\App\ObjectManager;

class CustomField implements RowCustomizerInterface
{
    /**
     * @var Export
     */
    private $export;

    /**
     * @var MergedAttributeProcessor
     */
    private $mergedAttributeProcessor;

    /**
     * @var array
     */
    private $processedFields = [];

    /**
     * @var CustomFieldsValidator|null
     */
    private $validator;

    /**
     * @var CustomFieldsProcessor|null
     */
    private $customFieldsProcessor;

    public function __construct(
        Export $export,
        ?CustomFieldsRepositoryInterface $cFieldsRepository, //@deprecated
        ?ProductRepositoryInterface $productRepository, //@deprecated
        ?FieldCollectionFactory $collectionFactory, //@deprecated
        MergedAttributeProcessor $mergedAttributeProcessor,
        CustomFieldsValidator $customFieldsValidator = null, //todo: move to not optional
        CustomFieldsProcessor $customFieldsProcessor = null //todo: move to not optional
    ) {
        $this->export = $export;
        $this->mergedAttributeProcessor = $mergedAttributeProcessor;
        $this->validator = $customFieldsValidator ?? ObjectManager::getInstance()
            ->get(CustomFieldsValidator::class);
        $this->customFieldsProcessor = $customFieldsProcessor ?? ObjectManager::getInstance()
            ->get(CustomFieldsProcessor::class);
    }

    public function prepareData($collection, $productIds)
    {
        if (!$this->checkValidator($collection)) {
            return;
        }
        $this->mergedAttributeProcessor->prepareAttrReplacements(
            $collection
        );
        foreach ($collection->getItems() as $product) {
            $this->processProduct($product);
        }
    }

    public function addHeaderColumns($columns)
    {
        return $columns;
    }

    public function addData($dataRow, $productId)
    {
        $dataRow['amasty_custom_data'][Export::PREFIX_CUSTOM_FIELD_ATTRIBUTE] = [];
        foreach ($this->processedFields[$productId] ?? [] as $code => $value) {
            $dataRow['amasty_custom_data'][Export::PREFIX_CUSTOM_FIELD_ATTRIBUTE][$code] = $value;
        }

        return $dataRow;
    }

    public function getAdditionalRowsCount($additionalRowsCount, $productId)
    {
        return $additionalRowsCount;
    }

    private function checkValidator(Collection $collection): bool
    {
        if ($this->export->hasAttributes(Export::PREFIX_CUSTOM_FIELD_ATTRIBUTE)) {
            $this->validator->setCustomFields(
                $this->export->getAttributesByType(Export::PREFIX_CUSTOM_FIELD_ATTRIBUTE)
            );

            return !empty($this->validator->prepareRules($collection));
        }

        return false;
    }

    private function processProduct(ProductInterface $product): void
    {
        foreach ($this->validator->getValidRules($product) as $code => $rule) {
            $this->processedFields[$product->getId()][$code] = $this->customFieldsProcessor->process($product, $rule);
        }
    }
}
