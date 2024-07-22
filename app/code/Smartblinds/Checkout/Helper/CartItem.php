<?php

namespace Smartblinds\Checkout\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Tax\Api\TaxCalculationInterface;

class CartItem extends AbstractHelper
{
    private TaxCalculationInterface $taxCalculation;
    private PriceHelper $priceHelper;

    public function __construct(
        Context $context,
        TaxCalculationInterface $taxCalculation,
        PriceHelper $priceHelper
    ) {
        $this->taxCalculation = $taxCalculation;
        $this->priceHelper = $priceHelper;
        parent::__construct($context);
    }

    public function getOriginalUnitPriceHtml(AbstractItem $item)
    {
        if ($originalPrice = $this->getOriginalPrice($item)) {
            $rate = $this->taxCalculation->getCalculatedRate($item->getProduct()->getData('tax_class_id'));
            return ''
                . '<span class="price-including-tax" data-label="<?= $block->escapeHtmlAttr(__(\'Incl. Tax\')) ?>">'
                . '<span class="cart-price">'
                . $this->priceHelper->currency($originalPrice + $originalPrice * $rate / 100)
                . '</span>'
                . '</span>';
        }
        return '';
    }

    public function needShowOriginalPrice(AbstractItem $item)
    {
        return ($item->getProduct()->getTypeId() === 'configurable'
                || ($item->getProduct()->getSku() === 'curtain_tracks'))
            && $this->getOriginalPrice($item) && ($item->getCalculationPrice() < $this->getOriginalPrice($item));
    }

    public function getOriginalPrice(AbstractItem $item)
    {
        $price = 0;
        $optionIds = $item->getProduct()->getCustomOption('option_ids');
        if (!$optionIds) {
            return null;
        }
        foreach (explode(',', $optionIds->getValue()) as $optionId) {
            /** @var \Magento\Catalog\Model\Product\Option $option */
            $option = $item->getProduct()->getOptionById($optionId);
            if (!$option) {
                continue;
            }

            $isMeasurementsOption = in_array($option->getData('option_code'), ['width_height', 'curtain_tracks_width']);

            $confItemOption = $item->getProduct()->getCustomOption('option_' . $option->getId());
            /** @var \Magento\Catalog\Model\Product\Option\Type\DefaultType $group */
            $group = $option->groupFactory($option->getType())
                ->setOption($option)
                ->setConfigurationItemOption($confItemOption);
            $group->setData('original_price', true);
            $optionPrice = $group->getOptionPrice($confItemOption->getValue(), $item->getProduct()->getData('final_price'));
            $group->setData('original_price', null);

            if ($optionPrice && !$isMeasurementsOption) {
                $price += $optionPrice;
                continue;
            }

            if (!$isMeasurementsOption) {
                continue;
            }

            $originalPrice = $group->getOriginalPrice($confItemOption->getValue(), $item->getProduct()->getData('final_price'));
            if ($optionPrice < $originalPrice) {
                $price += $originalPrice;
            }
        }
        return $price;
    }
}
