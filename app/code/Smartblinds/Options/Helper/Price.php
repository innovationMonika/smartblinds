<?php

namespace Smartblinds\Options\Helper;

class Price extends \MageWorx\OptionBase\Helper\Price
{
    public function getTaxPrice($product, $price, $includeTax = null)
    {
        if ($this->baseHelper->checkModuleVersion('100.1.6', '100.2.0', null, null, 'Magento_Tax') ||
            $this->baseHelper->checkModuleVersion('100.2.6', null, null, null, 'Magento_Tax')){
            if ($includeTax !== null) {
                $needUseShippingExcludeTax = $this->taxConfig->getNeedUseShippingExcludeTax();
                $this->taxConfig->setNeedUseShippingExcludeTax(true);
            }
        }

        $price = $this->catalogHelper->getTaxPrice(
            $product,
            $price,
            $includeTax,
            null,
            null,
            null,
            null,
            null,
            false // DISABLED PRICE ROUNDING FOR CORRECT FRONTEND AND BACKEND CALCULATION
            // WE NEED TO ROUND ONLY WHILE RENDERING
        );
        if (isset($needUseShippingExcludeTax)) {
            $this->taxConfig->setNeedUseShippingExcludeTax($needUseShippingExcludeTax);
        }
        return $price;
    }
}
