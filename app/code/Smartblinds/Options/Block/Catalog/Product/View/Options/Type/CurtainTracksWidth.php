<?php declare(strict_types=1);

namespace Smartblinds\Options\Block\Catalog\Product\View\Options\Type;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\CalculateCustomOptionCatalogRule;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Tax\Model\Config;
use Smartblinds\Options\Model\Product\Option\Type\CurtainTracksWidth as CurtainTracksWidthModel;

class CurtainTracksWidth extends \Magento\Catalog\Block\Product\View\Options\Type\Text
{
    private TaxCalculationInterface $taxCalculation;
    private Config $taxConfig;
    private CurtainTracksWidthModel $model;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Catalog\Helper\Data $catalogData,
        TaxCalculationInterface $taxCalculation,
        Config $taxConfig,
        CurtainTracksWidthModel $model,
        array $data = [],
        CalculateCustomOptionCatalogRule $calculateCustomOptionCatalogRule = null,
        CalculatorInterface $calculator = null,
        PriceCurrencyInterface $priceCurrency = null
    ) {
        $this->taxCalculation = $taxCalculation;
        $this->taxConfig = $taxConfig;
        $this->model = $model;
        parent::__construct(
            $context, $pricingHelper, $catalogData, $data,
            $calculateCustomOptionCatalogRule, $calculator, $priceCurrency
        );
    }

    public function getJson()
    {
        $product = $this->getProduct();
        /** @var Product $product */
        $productTaxClassId = $product->getData('tax_class_id');
        $taxRate = $this->taxCalculation->getCalculatedRate($productTaxClassId);

        if ($this->taxConfig->getPriceDisplayType() !== Config::DISPLAY_TYPE_INCLUDING_TAX) {
            $taxRate = 0;
        }

        return json_encode([
            'taxRate' => $taxRate,
            'prices' => $this->model->getPrices(),
            'regularPrice' => $product->getPriceInfo()->getPrice(RegularPrice::PRICE_CODE)->getAmount()->getValue(),
            'finalPrice' => $product->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getAmount()->getValue(),
        ]);
    }
}
