<?php declare(strict_types=1);

namespace Smartblinds\Options\Plugin\Catalog\Block\Product\View\Type\Configurable;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Directory\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Tax\Model\Config;

class AddTaxRate
{
    private Json $json;
    private TaxCalculationInterface $taxCalculation;
    private ScopeConfigInterface $scopeConfig;
    private Data $directoryHelper;
    private Config $taxConfig;

    public function __construct(
        Json $json,
        TaxCalculationInterface $taxCalculation,
        ScopeConfigInterface $scopeConfig,
        Data $directoryHelper,
        Config $taxConfig
    ) {
        $this->json = $json;
        $this->taxCalculation = $taxCalculation;
        $this->scopeConfig = $scopeConfig;
        $this->directoryHelper = $directoryHelper;
        $this->taxConfig = $taxConfig;
    }

    public function afterGetJsonConfig(
        Configurable $subject,
        $result
    ) {
        $product = $subject->getProduct();

        $resultDecoded = $this->json->unserialize($result);

        /** @var Product $product */
        $productTaxClassId = $product->getData('tax_class_id');
        $taxRate = $this->taxCalculation->getCalculatedRate($productTaxClassId);

        if ($this->taxConfig->getPriceDisplayType() !== Config::DISPLAY_TYPE_INCLUDING_TAX) {
            $taxRate = 0;
        }
        $resultDecoded['taxRate'] = $taxRate;

        return $this->json->serialize($resultDecoded);
    }
}
