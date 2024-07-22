<?php declare(strict_types=1);

namespace Smartblinds\Options\Plugin\MageWorx\OptionBase\Block\Product\View\Options;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json;
use MageWorx\OptionBase\Block\Product\View\Options;
use MageWorx\OptionBase\Helper\Price;
use MageWorx\OptionFeatures\Helper\Data as Helper;

class AddSmartblindsPrice
{
    private Json $json;
    private PriceCurrencyInterface $priceCurrency;
    private Price $basePriceHelper;

    public function __construct(
        Json $json,
        PriceCurrencyInterface $priceCurrency,
        Price $basePriceHelper
    ) {
        $this->json = $json;
        $this->priceCurrency = $priceCurrency;
        $this->basePriceHelper = $basePriceHelper;
    }

    public function afterGetProductJsonConfig(
        Options $subject,
        $result
    ) {
        $resultDecoded = $this->json->unserialize($result);

        $product = $subject->getProduct();

        if ($resultDecoded['type_id'] !== Configurable::TYPE_CODE) {
            return $result;
        }

        $resultDecoded['regular_price_excl_tax'] = $this->priceCurrency->convert(
            $this->getSmartblindsPrice($product, false)
        );
        $resultDecoded['regular_price_incl_tax'] = $this->priceCurrency->convert(
            $this->getSmartblindsPrice($product, true)
        );
        $resultDecoded['final_price_excl_tax'] = $this->priceCurrency->convert(
            $this->getSmartblindsPrice($product, false)
        );
        $resultDecoded['final_price_incl_tax'] = $this->priceCurrency->convert(
            $this->getSmartblindsPrice($product, true)
        );

        $resultEncoded = $this->json->serialize($resultDecoded);

        return $resultEncoded;
    }

    private function getSmartblindsPrice($product, $includeTax = null)
    {
        return $this->basePriceHelper->getTaxPrice($product, $product->getData('smartblinds_price'), $includeTax);
    }
}
