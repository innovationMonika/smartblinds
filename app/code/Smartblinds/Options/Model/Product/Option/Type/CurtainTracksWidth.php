<?php declare(strict_types=1);

namespace Smartblinds\Options\Model\Product\Option\Type;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option\Type\Text;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice;

class CurtainTracksWidth extends Text
{
    const GROUP_CODE = 'curtain_tracks_width';
    const TYPE_CODE  = 'curtain_tracks_width';

    public function getOptionPrice($optionValue, $basePrice)
    {
        $price = $this->getPrice($optionValue, $basePrice);

        /** @var Product $product */
        $product = $this->getConfigurationItemOption()->getProduct();

        $discount = 1;
        if ($product) {
            $finalPrice = $product->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getAmount()->getValue();
            $oldPrice = $product->getPriceInfo()->getPrice(RegularPrice::PRICE_CODE)->getAmount()->getValue();
            $discount = $finalPrice / $oldPrice;
        }

        return $price * $discount;
    }

    public function getOriginalPrice($optionValue, $basePrice)
    {
        return $this->getPrice($optionValue, $basePrice);
    }

    public function getPrices()
    {
        return [
            100 => 326.78,
            150 => 351.78,
            200 => 364.28,
            250 => 376.78,
            290 => 376.78,
            300 => 381.78,
            350 => 406.78,
            400 => 419.28,
            450 => 431.78,
            500 => 446.78,
            550 => 456.78,
            600 => 481.78,
        ];
    }

    private function getPrice($optionValue, $basePrice)
    {
        foreach ($this->getPrices() as $possibleWidth => $possiblePrice) {
            if ($optionValue <= $possibleWidth) {
                return $possiblePrice;
            }
        }
        return 0;
    }
}
