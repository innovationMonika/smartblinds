<?php

namespace GoMage\CatalogDiscountLabels\Helper;

use GoMage\CatalogDiscountLabels\Model\ResourceModel\MinimalPrice;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Tax\Model\Config;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_NOTIFICATION_BAR_BLOCK_ID = 'gomage_discount_labels/general/block_id';

    private MinimalPrice $minimalPrice;
    private TaxCalculationInterface $taxCalculation;
    private Config $taxConfig;

    public function __construct(
        Context $context,
        MinimalPrice $minimalPrice,
        TaxCalculationInterface $taxCalculation,
        Config $taxConfig
    ) {
        parent::__construct($context);
        $this->minimalPrice = $minimalPrice;
        $this->taxCalculation = $taxCalculation;
        $this->taxConfig = $taxConfig;
    }

    public function displayDiscountLabel(Product $product): ?string
    {
        $discount = $product->getData('discount');
        return is_numeric($discount) && $discount > 0 ?
            "{$discount}%" : (!empty($discount) ? $discount : null);
    }

    /**
     * @param Category $currentCategory
     * @param AbstractCollection|null $productCollection
     * @return string
     * @throws LocalizedException
     */
    public function getPriceFrom(Category $currentCategory, $productCollection = null)
    {
        $currency = $currentCategory->getStore()->getCurrentCurrency();

        if (!$currentCategory->hasData('minimal_product_price')) {
            $result = $this->minimalPrice->loadTaxClassIdWithPrice($currentCategory->getId());
            list($taxClassId, $price) = $result;
            if ($this->taxConfig->getPriceDisplayType() === Config::DISPLAY_TYPE_INCLUDING_TAX) {
                $rate = $this->taxCalculation->getCalculatedRate($taxClassId);
                $price += $price * $rate / 100;
            }
            $currentCategory->setData('minimal_product_price', $price);
        }
        return $currency->formatTxt($currentCategory->getData('minimal_product_price'));
    }

    public function displayNotificationBlock()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_NOTIFICATION_BAR_BLOCK_ID,
            ScopeInterface::SCOPE_STORE
        );
    }
}
