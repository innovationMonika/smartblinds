<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\MultiFees\Model\Total\Creditmemo;

use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;
use MageWorx\MultiFees\Helper\Data as HelperData;

/**
 * Class ProductFee
 */
class ProductFee extends AbstractTotal
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * ProductFee constructor.
     *
     * @param HelperData $helperData
     * @param array $data
     */
    public function __construct(HelperData $helperData, array $data = [])
    {
        parent::__construct($data);
        $this->helperData = $helperData;
    }

    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this|void
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $order            = $creditmemo->getOrder();
        $orderFeeInvoice  = (float)$order->getMageworxProductFeeInvoiced();
        $orderFeeRefunded = (float)$order->getMageworxProductFeeRefunded();

        if ((float)$order->getMageworxProductFeeAmount() > 0 && $orderFeeRefunded < $orderFeeInvoice) {
            $feeDetails = $order->getMageworxProductFeeDetails();

            if ($feeDetails) {
                $feeDetails = $this->helperData->unserializeValue($feeDetails);
                $feeAmount  = $feeBaseAmount = $feeTaxAmount = $feeBaseTaxAmount = 0.0;

                foreach ($feeDetails as $fee) {
                    foreach ($fee as $quoteItemId => $feeDetail) {
                        $orderItem      = $order->getItemByQuoteItemId($quoteItemId);
                        $creditmemoItem = $this->getCreditmemoItemByOrderItem($creditmemo, $orderItem);

                        if ($orderItem && $creditmemoItem) {
                            $qty              = $creditmemoItem->getQty() / $orderItem->getQtyOrdered();
                            $feeAmount        += $qty * (float)$feeDetail['price'];
                            $feeBaseAmount    += $qty * (float)$feeDetail['base_price'];
                            $feeTaxAmount     += $qty * (float)$feeDetail['tax'];
                            $feeBaseTaxAmount += $qty * (float)$feeDetail['base_tax'];
                        }
                    }
                }
            } else {
                $baseFeeInvoice  = (float)$order->getBaseMageworxProductFeeInvoiced();
                $baseFeeRefunded = (float)$order->getBaseMageworxProductFeeRefunded();

                $feeAmount        = $orderFeeInvoice - $orderFeeRefunded;
                $feeBaseAmount    = $baseFeeInvoice - $baseFeeRefunded;
                $feeTaxAmount     = $order->getMageworxProductFeeTaxAmount();
                $feeBaseTaxAmount = $order->getBaseMageworxProductFeeTaxAmount();
            }

            $creditmemo->setMageworxProductFeeAmount($feeAmount);
            $creditmemo->setBaseMageworxProductFeeAmount($feeBaseAmount);
            $creditmemo->setMageworxProductFeeTaxAmount($feeTaxAmount);
            $creditmemo->setBaseMageworxProductFeeTaxAmount($feeBaseTaxAmount);
            $creditmemo->setMageworxProductFeeDetails($order->getMageworxProductFeeDetails());

            if (!$creditmemo->isLast()) { //if is last creditmemo then fee tax already included in grand total
                $creditmemo->setBaseGrandTotal(
                    (float)$creditmemo->getBaseGrandTotal() +
                    (float)$creditmemo->getBaseMageworxProductFeeAmount()
                );
                $creditmemo->setGrandTotal(
                    (float)$creditmemo->getGrandTotal() +
                    (float)$creditmemo->getMageworxProductFeeAmount()
                );
            } else {
                $creditmemo->setBaseGrandTotal(
                    (float)$creditmemo->getBaseGrandTotal() +
                    (float)$creditmemo->getBaseMageworxProductFeeAmount() -
                    (float)$creditmemo->getBaseMageworxProductFeeTaxAmount()
                );
                $creditmemo->setGrandTotal(
                    (float)$creditmemo->getGrandTotal() +
                    (float)$creditmemo->getMageworxProductFeeAmount() -
                    (float)$creditmemo->getMageworxProductFeeTaxAmount()
                );
            }
            if ($order->getBaseTaxAmount() > $creditmemo->getBaseTaxAmount()) {
                $creditmemo->setBaseTaxAmount(
                    (float)$creditmemo->getBaseTaxAmount() + (float)$order->getBaseMageworxProductFeeTaxAmount()
                );
            }
            if ($order->getTaxAmount() > $creditmemo->getTaxAmount()) {
                $creditmemo->setTaxAmount(
                    (float)$creditmemo->getTaxAmount() + (float)$order->getMageworxProductFeeTaxAmount()
                );
            }
        } else {
            $creditmemo->setMageworxProductFeeAmount(0);
            $creditmemo->setBaseMageworxProductFeeAmount(0);
            $creditmemo->setMageworxProductFeeTaxAmount(0);
            $creditmemo->setBaseMageworxProductFeeTaxAmount(0);
            $creditmemo->setMageworxProductFeeDetails('');
        }

        return $this;
    }

    /**
     * @param CreditmemoInterface $creditmemo
     * @param OrderItemInterface $orderItem
     * @return null|CreditmemoItemInterface
     */
    protected function getCreditmemoItemByOrderItem(CreditmemoInterface $creditmemo, OrderItemInterface $orderItem)
    {
        foreach ($creditmemo->getItems() as $item) {
            if ($item->getOrderItemId() == $orderItem->getItemId()) {
                return $item;
            }
        }

        return null;
    }
}