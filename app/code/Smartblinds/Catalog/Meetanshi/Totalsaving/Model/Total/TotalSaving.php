<?php

namespace Smartblinds\Catalog\Meetanshi\Totalsaving\Model\Total;

use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Meetanshi\Totalsaving\Helper\Data;
use Meetanshi\Totalsaving\Model\Config\Source\DisplaySavingOption;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class TotalSaving
 */
class TotalSaving extends \Meetanshi\Totalsaving\Model\Total\TotalSaving
{
    /**
     * @var ProductFactory
     */
    private $productFactory;
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var
     */
    protected $items;
    protected $currencyFactory;
    protected $storeManagerInterface;

    /**
     * Total Saving constructor.
     * @param Data $helper
     * @param ProductFactory $productFactory
     */
    public function __construct(
        Data $helper,
        ProductFactory $productFactory,
        CurrencyFactory $currencyFactory,
        StoreManagerInterface $storeManagerInterface
    ) {
        $this->setCode('total_saving');
        $this->productFactory = $productFactory;
        $this->currencyFactory = $currencyFactory;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->helper = $helper;
        parent::__construct($helper, $productFactory, $currencyFactory, $storeManagerInterface);
    }

    /**
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        $this->items = $shippingAssignment->getItems();

        if (!count($this->items)) {
            return $this;
        }
        $amount = 0;

        $total->setTotalAmount('total_saving', $amount);
        $total->setBaseTotalAmount('total_saving', $amount);
        $total->setTotalSavingAmount($this->getTotalSavingForMultiShipping($shippingAssignment, $total));
        $total->setBaseTotalSavingAmount($amount);
        $total->setGrandTotal($total->getGrandTotal() + $amount);
        $total->setBaseGrandTotal($total->getBaseGrandTotal() + $amount);
        return $this;
    }

    /**
     * @param Total $total
     */
    protected function clearValues(Total $total)
    {
        $total->setTotalAmount('subtotal', 0);
        $total->setBaseTotalAmount('subtotal', 0);
        $total->setTotalAmount('tax', 0);
        $total->setBaseTotalAmount('tax', 0);
        $total->setTotalAmount('discount_tax_compensation', 0);
        $total->setBaseTotalAmount('discount_tax_compensation', 0);
        $total->setTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setBaseTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setSubtotalInclTax(0);
        $total->setBaseSubtotalInclTax(0);
    }

    /**
     * @param Quote $quote
     * @param Total $total
     * @return array
     * @throws NoSuchEntityException
     */
    public function fetch(
        Quote $quote,
        Total $total
    ) {
        $quote->getStore()->getId();
        if ($quote->isMultipleShippingAddresses()) {
            $totalSaving = $total->getTotalSavingAmount();
        } else {
            $totalSaving = $this->getTotalSaving($quote, $total);
        }
        return [
            'code' => $this->getCode(),
            'title' => $this->helper->getSavingText(),
            'value' => $totalSaving,
            'area' => 'footer'
        ];
    }

    /**
     * @return Phrase
     * @throws NoSuchEntityException
     */
    public function getLabel()
    {
        return __($this->helper->getSavingText());
    }

    /**
     * @param Quote $quote
     * @param Total $total
     * @return float|int
     */
    public function getTotalSaving(Quote $quote, Total $total)
    {
        try {
            $items = $quote->getItems();
            $productPriceTotal = 0;
            $subTotalWithDiscount = 0;

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
            $smartblindHelper =  $objectManager->get(\Smartblinds\Catalog\Helper\Data::class);

            foreach ($items as $item) {
                $currencyCodeTo = $this->storeManagerInterface->getStore()->getCurrentCurrency()->getCode();
                $currencyCodeFrom = $this->storeManagerInterface->getStore()->getBaseCurrency()->getCode();

                $rate = $this->currencyFactory->create()->load($currencyCodeFrom)->getAnyRate($currencyCodeTo);
                //$itemAmount = $item->getProduct()->getPrice() * $rate;
                $itemAmount = number_format($item->getPriceInclTax(), 2);

                $productPriceTotal += $itemAmount * $item->getQty();

                $productId = $item->getProductId();
                $product = $productRepository->getById($productId);
                $discountPercent = str_replace(["%", "-"], "", $smartblindHelper->getDiscountPercent($product));
                $discountPercent = (float)$discountPercent; // Cast $discountPercent to a float
                $subTotalWithDiscount +=  ($itemAmount * $item->getQty()) / (1 - ( $discountPercent / 100)) ;
            }
          //  $subTotalWithDiscount = $total->getGrandTotal();
            $savingAmount = ($productPriceTotal - $subTotalWithDiscount);

            if ($productPriceTotal == 0) {
                return 0;
            }
            $savingPercentage = (100 - (($subTotalWithDiscount * 100) / $productPriceTotal));
            //$savingPercentage = $discountPercent;
            if ($this->helper->getDisplaySavingType() == DisplaySavingOption::DISPLAY_AS_AMOUNT) {
                return $savingAmount;
            } else {
                return (int)$savingPercentage;
            }
        } catch (NoSuchEntityException $e) {
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->info($e->getMessage());
            return 0;
        }
        return 0;
    }

    public function getTotalSavingForMultiShipping(ShippingAssignmentInterface $shippingAssignment, Total $total)
    {
        try {

            $items = $shippingAssignment->getItems();

            $productPriceTotal = 0;
            foreach ($items as $item) {
                $currencyCodeTo = $this->storeManagerInterface->getStore()->getCurrentCurrency()->getCode();
                $currencyCodeFrom = $this->storeManagerInterface->getStore()->getBaseCurrency()->getCode();

                $rate = $this->currencyFactory->create()->load($currencyCodeFrom)->getAnyRate($currencyCodeTo);
                $itemAmount = $item->getProduct()->getPrice() * $rate;

                $productPriceTotal += $itemAmount * $item->getQty();
            }


            $subTotalWithDiscount = $total->getSubtotalWithDiscount();
            $savingAmount = ($productPriceTotal - $subTotalWithDiscount);


            if ($productPriceTotal == 0) {
                return 0;
            }
            $savingPercentage = (100 - (($subTotalWithDiscount * 100) / $productPriceTotal));

            if ($this->helper->getDisplaySavingType() == DisplaySavingOption::DISPLAY_AS_AMOUNT) {
                return $savingAmount;
            } else {
                return $savingPercentage;
            }
        } catch (NoSuchEntityException $e) {
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->info($e->getMessage());
            return 0;
        }
    }
}
