<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */

namespace Amasty\Feed\Model\Export\RowCustomizer;

use Amasty\Feed\Model\Export\Product;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

class Effectivedate implements RowCustomizerInterface
{
    public const DS = DIRECTORY_SEPARATOR;
    public const START_UNIX_DATE = '1978-01-01T00:00';
    public const END_UNIX_DATE = '2038-01-01T00:00';
    public const SALE_PRICE_EFFECITVEDATE_INDEX = 'sale_price_effective_date';

    /**
     * @var StoreManagerInterface
     */
    private $timezone;

    /**
     * @var array
     */
    protected $effectiveDates = [];

    public function __construct(
        ?StoreManagerInterface $storeManager, //@deprecated
        ?ProductRepositoryInterface $productRepository, //@deprecated
        TimezoneInterface $timezone
    ) {
        $this->timezone = $timezone;
    }

    /**
     * Init array of effective date
     *
     * @inheritdoc
     */
    public function prepareData($collection, $productIds)
    {
        $productCollection = $this->prepareProductCollection($collection);
        foreach ($productCollection as $item) {
            $specialFromDate = $item->getSpecialFromDate();
            $specialToDate = $item->getSpecialToDate();
            if ($specialFromDate || $specialToDate) {
                $this->effectiveDates[$item->getId()] = $this->getSpecialEffectiveDate(
                    $specialFromDate,
                    $specialToDate
                );
            }
        }
    }

    /**
     * Init array of effective date
     *
     * @inheritdoc
     */
    public function addData($dataRow, $productId)
    {
        $customData = &$dataRow['amasty_custom_data'];

        if (isset($this->effectiveDates[$productId])) {
            $customData[Product::PREFIX_OTHER_ATTRIBUTES] = [
                self::SALE_PRICE_EFFECITVEDATE_INDEX => $this->effectiveDates[$productId]
            ];
        } else {
            $customData[Product::PREFIX_OTHER_ATTRIBUTES] = [
                self::SALE_PRICE_EFFECITVEDATE_INDEX => ""
            ];
        }

        return $dataRow;
    }

    /**
     * Columns are added to header
     *
     * @inheritdoc
     */
    public function addHeaderColumns($columns)
    {
        return $columns;
    }

    /**
     * Get number of additional rows
     *
     * @inheritdoc
     */
    public function getAdditionalRowsCount($additionalRowsCount, $productId)
    {
        return $additionalRowsCount;
    }

    private function prepareProductCollection(Collection $collection): Collection
    {
        $productCollection = clone $collection;
        $productCollection->clear();
        $productCollection->applyFrontendPriceLimitations();
        $productCollection->addAttributeToSelect([
            'price',
            'special_price',
            'special_from_date',
            'special_to_date'
        ]);
        $productCollection->addAttributeToFilter('special_price', ['notnull' => true]);

        return $productCollection;
    }

    /**
     * Get special effective date
     *
     * @param string $specialFromDate
     * @param string $specialToDate
     *
     * @return string
     */
    private function getSpecialEffectiveDate($specialFromDate, $specialToDate)
    {
        return $this->getSpecialFromDate($specialFromDate) . self::DS . $this->getSpecialToDate($specialToDate);
    }

    /**
     * Get first part of effective date
     *
     * @param string $specialFromDate
     *
     * @return string
     */
    private function getSpecialFromDate($specialFromDate = null)
    {
        $timeZoneValue = $this->timezone->getConfigTimezone();
        $timeZone = new \DateTimeZone($timeZoneValue);
        $dateValue = new \DateTime(self::START_UNIX_DATE, $timeZone);

        if ($specialFromDate) {
            $dateValue = new \DateTime($specialFromDate);
        }

        return $dateValue->format('Y-m-d\TH:iP');
    }

    /**
     * Get second part of effective date
     *
     * @param string $specialToDate
     *
     * @return string
     */
    private function getSpecialToDate($specialToDate = null)
    {
        $timeZoneValue = $this->timezone->getConfigTimezone();
        $timeZone = new \DateTimeZone($timeZoneValue);
        $dateValue = new \DateTime(self::END_UNIX_DATE);

        if ($specialToDate) {
            $dateValue = new \DateTime($specialToDate, $timeZone);
        }

        return $dateValue->format('Y-m-d\TH:iP');
    }
}
