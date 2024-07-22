<?php

namespace Smartblinds\Options\Block\Product\View;

use Magento\Catalog\Model\Product\Option\Value;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Framework\App\ObjectManager;
use Smartblinds\Options\Helper\Price;

class Options extends \MageWorx\OptionBase\Block\Product\View\Options
{
    protected function _getPriceConfiguration($option)
    {
        $optionPrice = $option->getPrice(true);
        if ($option->getPriceType() !== Value::TYPE_PERCENT) {
            $optionPrice = $this->pricingHelper->currency($optionPrice, false, false);
        }

        $data = [
            'prices' => [
                'oldPrice' => [
                    'amount' => $this->pricingHelper->currency($option->getRegularPrice(), false, false),
                    'amount_incl_tax' => $this->basePriceHelper->getTaxPrice(
                        $option->getProduct(),
                        $option->getRegularPrice(),
                        true
                    ),
                    'amount_excl_tax' => $this->basePriceHelper->getTaxPrice(
                        $option->getProduct(),
                        $option->getRegularPrice(),
                        false
                    ),
                    'adjustments' => [],
                ],
                'basePrice' => [
                    'amount' => $this->basePriceHelper->getTaxPrice(
                        $option->getProduct(),
                        $optionPrice,
                        false
                    ),
                ],
                'finalPrice' => [
                    'amount' => $this->basePriceHelper->getTaxPrice(
                        $option->getProduct(),
                        $optionPrice,
                        true
                    ),
                ],
            ],
            'type' => $option->getPriceType(),
            'name' => $option->getTitle(),
        ];
        return $data;
    }
}
