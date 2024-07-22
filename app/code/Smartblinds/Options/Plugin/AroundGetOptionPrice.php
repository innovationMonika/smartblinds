<?php

namespace Smartblinds\Options\Plugin;

use Magento\Backend\Model\Session\Quote;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Tax\Helper\Data as TaxHelper;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use MageWorx\OptionBase\Helper\Price as BasePriceHelper;
use MageWorx\OptionFeatures\Helper\Data as Helper;
use MageWorx\OptionFeatures\Model\Price as AdvancedPricingPrice;
use Smartblinds\Options\Model\Config;
use Smartblinds\Options\Plugin\Quote\Model\Quote\Address\Total\Subtotal\QuoteStorage;

class AroundGetOptionPrice extends \MageWorx\OptionFeatures\Plugin\AroundGetOptionPrice
{
    private AttributeRepositoryInterface $attributeRepository;
    private Config $config;
    private QuoteStorage $quoteStorage;

    private $subject;

    public function __construct(
        Helper $helper,
        BaseHelper $baseHelper,
        BasePriceHelper $basePriceHelper,
        TaxHelper $taxHelper,
        StoreManager $storeManager,
        AdvancedPricingPrice $advancedPricingPrice,
        PricingHelper $pricingHelper,
        ObjectManagerInterface $objectManager,
        AttributeRepositoryInterface $attributeRepository,
        Config $config,
        QuoteStorage $quoteStorage
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->config = $config;
        $this->quoteStorage = $quoteStorage;
        parent::__construct(
            $helper, $baseHelper, $basePriceHelper, $taxHelper, $storeManager, $advancedPricingPrice,
            $pricingHelper, $objectManager);
    }

    public function aroundGetOptionPrice($subject, $proceed, $optionValue, $basePrice)
    {
        $this->subject = $subject;
        $option = $subject->getOption();
        $result = 0;

        $optionsQty = $this->getBuyRequestOptionsQty($subject);

        if (!$this->isSingleSelection($option)) {
            foreach (explode(',', $optionValue) as $value) {
                $qty = $this->getOptionQty($optionsQty, $option, $value);
                $_result = $option->getValueById($value);
                if ($_result) {
                    $price = $this->getFinalPrice($option, $_result, $subject);
                    $result += $this->getChargeableOptionPrice(
                        $price,
                        $qty
                    );
                } else {
                    if ($subject->getListener()) {
                        $subject->getListener()->setHasError(true)->setMessage($this->getWrongConfigurationMessage());
                        break;
                    }
                }
            }
        } elseif ($this->isSingleSelection($option)) {
            $qty = $this->getOptionQty($optionsQty, $option, $optionValue);
            $_result = $option->getValueById($optionValue);
            if ($_result) {
                $price = $this->getFinalPrice($option, $_result, $subject);
                $result = $this->getChargeableOptionPrice(
                    $price,
                    $qty
                );
            } else {
                if ($subject->getListener()) {
                    $subject->getListener()->setHasError(true)->setMessage($this->getWrongConfigurationMessage());
                }
            }
        }

        return $result;
    }

    private function getFinalPrice($option, $_result)
    {
        $product = $option->getProduct();
        $price = $this->advancedPricingPrice->getPrice($option, $_result);
        $discount = 1;
        if ($product && !$this->subject->getData('original_price')) {
            $finalPrice = $product->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getAmount()->getValue();
            $oldPrice = $product->getPriceInfo()->getPrice(RegularPrice::PRICE_CODE)->getAmount()->getValue();
            $discount = $oldPrice > 0 ? $finalPrice / $oldPrice : 1;
        }
        $price *= $discount;
        return $price;
    }

    protected function getOptionQty($optionsQty, $option, $optionValue)
    {
        if ($this->isBeidingOption($option) && $this->isTdbuSelected()) {
            return 2;
        }

        $qty = 1;
        if (isset($optionsQty[$option->getOptionId()])) {
            if (!is_array($optionsQty[$option->getOptionId()])) {
                $qty = $optionsQty[$option->getOptionId()];
            } else {
                if (isset($optionsQty[$option->getOptionId()][$optionValue])) {
                    $qty = $optionsQty[$option->getOptionId()][$optionValue];
                }
            }
        }

        return $qty;
    }

    private function isBeidingOption($option)
    {
        return $option->getData('option_code') == $this->config->getBedieningOptionCode();
    }

    private function isTdbuSelected()
    {
        $configurationItemOption = $this->subject->getConfigurationItemOption();
        if ($configurationItemOption) {
            $quoteItem = $configurationItemOption->getItem();
            if ($quoteItem) {
                $buyRequest = $quoteItem->getBuyRequest();
                if ($buyRequest) {
                    $systemTypeAttributeId = $this->attributeRepository->get('catalog_product', 'system_type')->getAttributeId();
                    $systemTypeOptionTdbuId = $this->config->getSystemTypeTdbuOptionId();
                    return ($buyRequest->getData('super_attribute')[$systemTypeAttributeId] ?? '-1') == $systemTypeOptionTdbuId;
                }
            }
        }

        return false;
    }
}
