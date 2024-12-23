<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\MultiFees\Block\Totals\Cart2Quote;

use Magento\Sales\Model\Order as Source;
use MageWorx\MultiFees\Block\Totals\AbstractTotal;

/**
 * Class Fee
 *
 * Renders fee's totals in the order totals (incl. emails)
 *
 */
class Fee extends AbstractTotal
{
    /**
     * @return Source
     */
    protected function getRealSource()
    {
        $totalsBlock = $this->getParentTotalsBlock();
        $source      = $totalsBlock->getQuote();

        return $source;
    }

    /**
     * Add MageWorx Fee Amount to Order
     */
    public function initTotals()
    {
        $totalsBlock = $this->getParentTotalsBlock();
        $source      = $this->getRealSource();
        $storeId     = $source->getStoreId();

        $feeDetails = $this->getFeeDetails($source);
        if ($feeDetails && $this->helperData->expandFeeDetailsInPdf($storeId)) {
            $feesAsArray = $this->helperData->unserializeValue($feeDetails);
            $totals      = $this->getExpandedFeeTotals($feesAsArray);
        } else {
            $totals = $this->getRegularFeeTotals($source);
        }

        $before = 'grand_total';
        foreach ($totals as $total) {
            $totalsBlock->addTotalBefore(
                new \Magento\Framework\DataObject(
                    $total
                ),
                $before
            );

            $before = $total['code'];
        }
    }
}
