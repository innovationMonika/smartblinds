<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Model\Plugin\Klarna;

use Klarna\Core\Helper\KlarnaConfig;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class AddFeeAsSurcharge
{
    /**
     * @var KlarnaConfig
     */
    protected $klarnaConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * AddFeeAsSurcharge constructor.
     *
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager, ObjectManagerInterface $objectManager)
    {
        $this->storeManager = $storeManager;

        //workaround when klarna modules are removed
        $this->klarnaConfig = $objectManager->get(KlarnaConfig::class);
    }

    /**
     * @param \Klarna\Core\Model\Api\Builder $subject
     * @param array $orderLines
     * @return array
     * @throws \Klarna\Core\Exception
     * @throws NoSuchEntityException
     */
    public function afterGetOrderLines($subject, $orderLines = [])
    {
        $quote = $subject->getObject();
        if (!$quote) {
            return [$orderLines];
        }

        $address = $quote->getIsVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();
        if (!$address) {
            return [$orderLines];
        }

        $feeAmount           = (float)$address->getBaseMageworxFeeAmount();
        $feeTax              = (float)$address->getBaseMageworxFeeTaxAmount();
        $feeAmountWithoutTax = $feeAmount - $feeTax;
        $overallTaxPercent   = $feeAmountWithoutTax != 0 ? $feeTax / $feeAmountWithoutTax * 100 : 0;

        // Regular fees
        $feeOrderLine = [
            'type'      => 'surcharge',
            'reference' => 'multifees',
            'name'      => 'multifees',
            'quantity'  => 1
        ];

        if ($this->klarnaConfig->isSeparateTaxLine($this->storeManager->getStore())) {
            $feeOrderLine['unit_price']       = $this->toApiFloat($feeAmountWithoutTax);
            $feeOrderLine['tax_rate']         = 0;
            $feeOrderLine['total_tax_amount'] = 0;
            $feeOrderLine['total_amount']     = $this->toApiFloat($feeAmountWithoutTax);
        } else {
            $feeOrderLine['unit_price']       = $this->toApiFloat($feeAmount);
            $feeOrderLine['tax_rate']         = $this->toApiFloat($overallTaxPercent);
            $feeOrderLine['total_tax_amount'] = $this->toApiFloat($feeTax);
            $feeOrderLine['total_amount']     = $this->toApiFloat($feeAmount);
        }

        $orderLines[] = $feeOrderLine;

        // Product fees
        $productFeeAmount            = (float)$address->getBaseMageworxProductFeeAmount();
        $productFeeTax               = (float)$address->getBaseMageworxProductFeeTaxAmount();
        $productFeeAmountWithoutTax  = $productFeeAmount - $productFeeTax;
        $productFeeOverallTaxPercent = $productFeeAmount != 0 ? $productFeeTax / $productFeeAmountWithoutTax * 100 : 0;

        $productFeeOrderLine = [
            'type'      => 'surcharge',
            'reference' => 'product_fees',
            'name'      => 'product_fees',
            'quantity'  => 1
        ];

        if ($this->klarnaConfig->isSeparateTaxLine($this->storeManager->getStore())) {
            $productFeeOrderLine['unit_price']       = $this->toApiFloat($productFeeAmountWithoutTax);
            $productFeeOrderLine['tax_rate']         = 0;
            $productFeeOrderLine['total_tax_amount'] = 0;
            $productFeeOrderLine['total_amount']     = $this->toApiFloat($productFeeAmountWithoutTax);
        } else {
            $productFeeOrderLine['unit_price']       = $this->toApiFloat($productFeeAmount);
            $productFeeOrderLine['tax_rate']         = $this->toApiFloat($productFeeOverallTaxPercent);
            $productFeeOrderLine['total_tax_amount'] = $this->toApiFloat($productFeeTax);
            $productFeeOrderLine['total_amount']     = $this->toApiFloat($productFeeAmount);
        }

        $orderLines[] = $productFeeOrderLine;

        return $orderLines;
    }

    /**
     * Prepare float for API call
     *
     * @param float $float
     *
     * @return int
     */
    private function toApiFloat(float $float)
    {
        return round($float * 100);
    }
}
