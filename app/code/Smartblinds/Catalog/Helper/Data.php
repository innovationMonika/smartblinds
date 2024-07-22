<?php

namespace Smartblinds\Catalog\Helper;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    public function getDiscountPercent(Product $product)
    {
        $regularPrice = $product->getPriceInfo()->getPrice(RegularPrice::PRICE_CODE)->getAmount()->getValue();
        $finalPrice = $product->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getAmount()->getValue();
        if ($finalPrice >= $regularPrice) {
            return '';
        }
        return '-' . round((1 - $finalPrice / $regularPrice) * 100) . '%';
    }
}
