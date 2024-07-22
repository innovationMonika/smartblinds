<?php declare(strict_types=1);

namespace Smartblinds\ConfigurableProduct\Pricing\Price;

use Magento\Framework\Pricing\Price\AbstractPrice;

class SmartblindsPrice extends AbstractPrice
{
    const PRICE_CODE = 'smartblinds_price';

    public function getValue()
    {
        if ($this->value === null) {
            $price = $this->product->getData(self::PRICE_CODE);
            $priceInCurrentCurrency = $this->priceCurrency->convertAndRound($price);
            $this->value = $priceInCurrentCurrency ? (float)$priceInCurrentCurrency : false;
        }
        return $this->value;
    }
}
